<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;


class SwgController extends Controller
{
    static public $enums = [];

    /**
     * {module_name}_swagger.php输出的目录
     * @var string
     */
    public $tmp_dir = "@app/runtime";


    /**
     * {module_name}_swagger.json输出的目录
     * @var string
     */
    public $out_dir = "/var/www/html";

    /**
     * swagger.json web目录
     * @var string
     */
    public $web_dir = "/var/www/html";

    /**
     * 是否拷贝输出的json作为web目录的swagger.json
     * @var boolean
     */
    public $as_swg_json = true;

    /**
     * 是否执行后续操作
     * @var boolean
     */
    public $after = false;

    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['tmp_dir', 'out_dir', 'update', 'as_swg_json', 'web_dir']
        );
    }
    /**
     * [init description]http://localhost/swg/swagger-ui/dist/
     * @return [type] [description]
     */
    public function init(){
        parent::init();
        Yii::setAlias("@doc", dirname(dirname((dirname(__DIR__)))) . '/doc');
        foreach($this->getAlias() as $alias => $path){
            Yii::setAlias($alias, $path);
        }
    }

    public function actionIndex($module){
        $files = $this->getFilesFromModule($module);
        $files = $this->prepareFiles($files);
        $refs = [];
        $apis = [];
        $root = [];
        foreach($files as $file){
            list($fileRefs, $fileApis, $fileRoot) = $this->parseDocesFormPhpFile($file);
            foreach($fileRefs as $refName => $ref){
                $refs[$refName] = $ref;
            }
            foreach($fileApis as $api){
                $apis[] = $api;
            }
            if($fileRoot){
                $root = array_merge($root, $fileRoot);
            }
        }
        if(!$root){
            throw new \Exception("root 定义不能为空");
        }

        $this->initPhpFile($module);

        $rootContent = $this->geneSwgRootContentFromDef($root, $module);
        $this->appendDocInPhpFile($rootContent, $module);
        foreach($refs as $ref){
            $refContent = $this->geneSwgRefContentFromDef($ref);
            $this->appendDocInPhpFile($refContent, $module);
        }
        foreach($apis as $api){
            $apiContent = $this->geneSwgApiContentFromDef($api, $refs);
            $this->appendDocInPhpFile($apiContent, $module);
        }
        $this->geneSwgJson($module);
        echo sprintf("php_swagger:%s\n", $this->getPhpFilePath($module));
        echo sprintf("json_swagger:%s\n", $this->getJsonFilePath($module));
        if($this->as_swg_json){
            copy($this->getJsonFilePath($module), sprintf("%s/swagger.json", $this->web_dir));
        }
        if($this->after){
            $this->afterGene($module);
        }
    }

    protected function afterGene($module){
        $cmd = ArrayHelper::getValue(Yii::$app->params['apiafter'], $module);
    }


    protected function geneSwgJson($module){
        $bin = dirname(dirname((Yii::getAlias('@app')))) . '/system/vendor/zircote/swagger-php/bin/swagger';
        system(sprintf("php %s %s --output %s", $bin, $this->getPhpFilePath($module), $this->getJsonFilePath($module)));
    }
    protected function appendDocInPhpFile($content, $module){
        file_put_contents($this->getPhpFilePath($module), $content . "\n\n", FILE_APPEND);
    }

    protected function getPhpFilePath($module){
        return Yii::getAlias(sprintf("%s/%s_swagger.php", $this->tmp_dir, $module));
    }

    protected function getJsonFilePath($module){
        return Yii::getAlias(sprintf("%s/%s_swagger.json", $this->out_dir, $module));
    }

    protected function initPhpFile($module){
        $path = $this->getPhpFilePath($module);
        file_put_contents($path, "<?php\n");
    }

    protected function geneSwgRefContentFromDef($ref){
        $tpl = <<<tpl
/**
 *  @SWG\Definition(
 *    definition="%s",
%s
 *  )
 */
tpl;
        $propsContents = [];
        $required = [];
        foreach($ref['props'] as $prop){
            $propsContents[] =  $this->geneSwgPropContentFromDef($prop);
            if('required' == $prop['required']){
                $required[] = "\"" . $prop['name'] . "\"";
            }
        }
        if($required){
            $propsContents[] = sprintf("*           required={%s}", implode(',', $required));
        }
        $propsContents = implode(",\n", $propsContents);
        return sprintf($tpl, $ref['name'], $propsContents);
    }


    protected function geneSwgApiContentFromDef($api, $refs){
        $varMap = [
            "{{method}}" => ucfirst($api['method']),
            "{{path}}" => $api['path'],
            "{{tag}}" => $api['tag'],
            "{{des}}" => $api['des'],

        ];
        $tpl = <<<tpl
/**
 *  @SWG\{{method}}(
 *    path="{{path}}",
 *    tags={"{{tag}}"},
 *    summary="{{des}}",
 *    produces={"application/json"},
 %s
 *  )
 */
tpl;
        $tpl = strtr($tpl, $varMap);

        // 构造参数
        // 获取所有in_body的参数
        $paramsContent = [];
        $normalProps = [];
        $bodyProps = [];
        foreach($api['params'] as $k=> $prop){
            switch ($prop['query_or_path']) {
                case 'in_body':
                    $bodyProps[] = $prop;
                    break;
                case 'in_path':
                case 'in_query':
                    $normalProps[] = $prop;
                    break;
                default:
                    throw new \Exception("不合法的query_or_path类型，第" . $k . '个参数');
                    break;
            }
        }
        if($bodyProps){
            $paramsContent[] = $this->geneSwgBodyParamsContentFromDef($bodyProps);
        }
        if($normalProps){
            $paramsContent = array_merge($paramsContent, $this->geneSwgNormalParamsContentFromDef($normalProps));
        }
        $content = [];
        if($paramsContent){
            $content[] = implode(",\n", $paramsContent);
        }
        $returnContent = $this->geneSwgReturnCotentFromDef($api, $refs);
        if($returnContent){
            $content[] = $returnContent;
        }
        return sprintf($tpl, implode(",\n", $content));
    }
    protected function geneSwgReturnCotentFromDef($api, $defs){
        $tpl = <<<tpl
*   	@SWG\Response(
*   		response=200,
*   		description="{{des}}",
*   		@SWG\Schema(
%s
*   		)
*   	)
tpl;
        $return = $api['return'];
        $tpl = strtr($tpl, [
            '{{des}}' => $return['des'],
        ]);
        if(!array_key_exists($return['ref'], $defs)){
            throw new \Exception("定义不存在" . $return['ref']);
        }
        if(!$return['props']){
            // 直接使用定义
            $tplContent = <<<tpl
*           type="object",
*           ref="#/definitions/%s"
tpl;
            $tplContent =  sprintf($tplContent, $return['ref']);
            return sprintf($tpl, $tplContent);
        }else{
            $return['props'] = ArrayHelper::index($return['props'], 'name');
            // 重新构造定义
            $ref = $defs[$return['ref']];
            $propsContent = [];
            foreach($ref['props'] as $prop){
                if(array_key_exists($prop['name'], $return['props'])){
                    $target = $return['props'][$prop['name']];
                    if(in_array($target['type'], ['object', 'array'])){
                        $prop['type'] = $target['type'];
                        $prop['ref'] = $target['ref'];
                    }else{
                        $prop['type'] = $target['type'];
                    }
                    $prop['des'] = $target['des'];
                }
                $propsContent[] = $this->geneSwgPropContentFromDef($prop);
            }
            $propsContent = implode(",\n", $propsContent);
        }
        return sprintf($tpl, $propsContent);
    }
    protected function geneSwgBodyParamsContentFromDef($bodyProps){
        $tpl = <<<tpl
 *  @SWG\Parameter(
 *    name="body",
 *    in="body",
 *    required=true,
 *    @SWG\Schema(
%s
 *    )
 *  )
tpl;
        $propsContents = [];
        $required = [];
        foreach($bodyProps as $prop){
            $propsContents[] =  $this->geneSwgParaPropContentFromDef($prop);
            if('required' == $prop['required']){
                $required[] = "\"" . $prop['name'] . "\"";
            }
        }
        if($required){
            $propsContents[] = sprintf("*           required={%s}", implode(',', $required));
        }
        $propsContents = implode(",\n", $propsContents);

        return sprintf($tpl, $propsContents);
    }

    protected function geneSwgNormalParamsContentFromDef($normalParams){
        $paramsContent = [];
        $tpl = <<<tpl
 *    @SWG\Parameter(
%s
 *    )
tpl;
        foreach($normalParams as $param){
            $varMap = [];
            $varMap['{{name}}'] = sprintf(" *      name=\"%s\"", $param['name']);
            $varMap['{{description}}'] = sprintf(" *      description=\"%s\"", $this->formatDes($param['des']));
            $varMap['{{required}}'] = sprintf(" *      required=%s", 'required' == $param['required'] ? 'true' : 'false');
            // 构造type
            if(in_array($param['type'], ['string', 'boolean'])){
                $content = <<<tpl
     *      type="%s"
tpl;
                $varMap['{{type}}'] = sprintf($content, $param['type']);
            }elseif('integer' == $param['type']){
                $content = <<<tpl
     *      type="integer",
     *      format="int32"
tpl;
                $varMap['{{type}}'] = $content;
            }else{
                throw new \Exception("parameter中不允许这中类型");
            }
            if($param['enum']){
                list($content, $helper) = $this->geneEnumContentFromStr($param['enum']);
                $varMap['{{enum}}'] = $content;
                $varMap['{{description}}'] = sprintf(" *      description=\"%s,%s\"", $param['des'], $helper);
            }
            $varMap['{{in}}'] = sprintf(" *      in=\"%s\"", 'in_query' == $param['query_or_path'] ? 'query' : 'path');
            $paramsContent[] = sprintf($tpl, implode(",\n", $varMap));
        }
        return $paramsContent;
    }
    protected static function getEnums(){
        if(!static::$enums){
            $data = spyc_load_file(Yii::getAlias('@doc/enums.yaml'));
            $result = [];
            foreach($data as $item){
                $enums = [];
                foreach($item['items'] as $value){
                    $def = explode('|', $value);
                    $def['value'] = $def[0];
                    $def['name'] = $def[1];
                    $def['des'] = ArrayHelper::getValue($def, 2, '');
                    $def['symbol'] = ArrayHelper::getValue($def, 3, '');
                    $def['isdefault'] = ArrayHelper::getValue($def, 4, '');
                    $value = $def;
                    $enums[] = sprintf("%s:%s", $value['value'], $value['name']);
                }
                $result[$item['field']] = implode('|', $enums);
            }
            static::$enums = $result;
        }
        return static::$enums;
    }
    protected function geneEnumContentFromStr($enumStr){
        if(preg_match("/is_enum\(\{\{([a-zA-Z0-9\_\-]+)\}\}\)/", $enumStr, $enumName)){
            // 说明得去构造enum值
            $enums = self::getEnums();
            if(!isset($enums[$enumName[1]])){
                throw new \Exception("指定的enum不存在" . $enumStr);
            }
            $enumStr = sprintf("is_enum(%s)", $enums[$enumName[1]]);
        }
        $r = preg_match("/is_enum\(([\s\S]+?)\)/", $enumStr, $matches);
        if(!$r){
            throw new \Exception("enum 语法错误");
        }
        $enumskv = explode('|', $matches[1]);
        $enums = [];
        foreach($enumskv as $item){
            list($value, ) = explode(':', $item);
            $enums[] = "\"" . $value . "\"";
        }
        return [
            sprintf(" *      enum={%s}", implode(',', $enums)),
            $matches[1]
        ];
    }

    protected function geneSwgParaPropContentFromDef($prop){
        $varMap = [
            "{{property}}" => '',
            "{{type}}" => '',
            "{{description}}" => ''
        ];
        $tpl = <<<tpl
 *    @SWG\Property(
%s
 *    )
tpl;
        $varMap['{{property}}'] = sprintf(" *      property=\"%s\"", $prop['name']);
        $varMap['{{description}}'] = sprintf(" *      description=\"%s\"", $prop['des']);
        if($prop['query_or_path'] != 'in_body'){
            $varMap['{{required}}'] = sprintf(" *      required=%s", 'required' == $prop['required'] ? 'true' : 'false');
        }
        // 构造type
        if(in_array($prop['type'], ['string', 'boolean'])){
            $content = <<<tpl
 *      type="%s"
tpl;
            $varMap['{{type}}'] = sprintf($content, $prop['type']);
        }elseif('integer' == $prop['type']){
            $content = <<<tpl
 *      type="integer",
 *      format="int32"
tpl;
            $varMap['{{type}}'] = $content;
        }elseif('array' == $prop['type']){
            if(in_array($prop['ref'], ['string', 'integer', 'boolean'])){
            $content = <<<tpl
 *      type="array",
 *      @SWG\Items(
 *      type="%s"
 *    )
tpl;
$varMap['{{type}}'] = sprintf($content, $prop['ref']);
            }else{
            $content = <<<tpl
 *      type="array",
 *      @SWG\Items(
 *      type="object",
 *      ref="#/definitions/%s"
 *    )
tpl;
$varMap['{{type}}'] = sprintf($content, $prop['ref']);
            }
        }elseif('object' == $prop['type']){
            $content = <<<tpl
 *      type="object",
 *      ref="#/definitions/%s"
tpl;
            $varMap['{{type}}'] = sprintf($content, $prop['ref']);
        }elseif('mixed' == $prop['type']){
            $content = <<<tpl
 *      type="string"
tpl;
            $varMap['{{type}}'] = $content;
        }


        if($prop['enum']){
            list($content, $helper) = $this->geneEnumContentFromStr($prop['enum']);
            $varMap['{{enum}}'] = $content;
            $varMap['{{description}}'] = sprintf(" *      description=\"%s,%s\"", $prop['des'], $helper);
        }
        return sprintf($tpl, implode(",\n", $varMap));
    }

    protected function formatDes($des){
        if(preg_match("/(\{\{enum_des:([0-9a-zA-Z\-\_]+)\}\})/", $des, $matches)){
            $enums = self::getEnums();
            if(array_key_exists($matches[2], $enums)){
                return strtr($des, [$matches['1'] => $enums[$matches[2]]]);
            }
            return $des;
        }
        return $des;
    }

    protected function geneSwgPropContentFromDef($prop){
        $varMap = [
            "{{property}}" => '',
            "{{type}}" => '',
            "{{description}}" => ''
        ];
        $tpl = <<<tpl
 *    @SWG\Property(
%s
 *    )
tpl;
        $varMap['{{property}}'] = sprintf(" *      property=\"%s\"", $prop['name']);
        $varMap['{{description}}'] = sprintf(" *      description=\"%s\"", $this->formatDes($prop['des']));
        // 构造type
        if(in_array($prop['type'], ['string', 'boolean'])){
            $content = <<<tpl
 *      type="%s"
tpl;
            $varMap['{{type}}'] = sprintf($content, $prop['type']);
        }elseif('integer' == $prop['type']){
            $content = <<<tpl
 *      type="integer",
 *      format="int32"
tpl;
            $varMap['{{type}}'] = $content;
        }elseif('array' == $prop['type']){
            $content = <<<tpl
 *      type="array",
 *      @SWG\Items(
 *      type="object",
 *      ref="#/definitions/%s"
 *    )
tpl;
            // todo 未必都是object
            $varMap['{{type}}'] = sprintf($content, $prop['ref']);
        }elseif('object' == $prop['type']){
            $content = <<<tpl
 *      type="object",
 *      ref="#/definitions/%s"
tpl;
            $varMap['{{type}}'] = sprintf($content, $prop['ref']);
        }elseif('mixed' == $prop['type']){
            $content = <<<tpl
 *      type="string"
tpl;
            $varMap['{{type}}'] = $content;
        }
        return sprintf($tpl, implode(",\n", $varMap));
    }

    protected function geneSwgRootContentFromDef($root, $module){
        $rootTempalte = <<<tpl
/**
 *  @SWG\Swagger(
 *    host="{{host}}",
 *    schemes={"{{schemes}}"},
 *    produces={"application/json"},
 *    consumes={"application/json"},
 *    basePath="{{basePath}}",
 *    @SWG\Info(
 *      version="{{version}}",
 *      title="{{title}}",
 *      description="{{description}}",
 *      @SWG\Contact({{Contact}}),
 *      @SWG\License({{License}})
 *    )
 *  )
 */
tpl;
        $varMap = [];
        foreach($root as $name => $value){
            if('description' == $name){
                $value = $this->formatRootDes($value, $module);
            }
            $varMap['{{'.$name.'}}'] = $value;
        }
        return strtr($rootTempalte, $varMap);
    }

    protected function formatRootDes($value, $module){
        $target = ArrayHelper::getValue(Yii::$app->params['apides'], $module, []);
        if(!$target){
            return $value;
        }
        return $target;
    }

    protected function parseDocesFormPhpFile($file){
        $content = file_get_contents($file);
        $result = preg_match_all("/\/\*[\s\S]*?\*\//", $content, $matches);
        if(!$result){
            return [[], [], []];
        }
        $defs = [];
        $apis = [];
        $root = [];
        foreach($matches[0] as $docBlock){
            $type = $this->getDocContentType($docBlock);
            switch ($type) {
                case 'def':
                    foreach($this->parseDefFromDocBlock($docBlock) as $def){
                        $defs[$def['name']] = $def;
                    }
                    break;
                case 'api':
                    $apis[] = $this->parseApiFormDocBlock($docBlock);
                    break;
                case 'root':
                    $root = array_merge($root, $this->parseRootFromDocBlock($docBlock));
                    break;
                default:
                    break;
            }
        }
        return [$defs, $apis, $root];
    }
    protected function parseRootFromDocBlock($docBlock){
        $result = preg_match_all(sprintf("/%s/",
        "\*\s*-\s*([a-zA-Z0-9\_\-]+?)\s+([\s\S]+?)\n+"
        ), $docBlock, $matches);
        if(!$result){
            throw new \Exception("root 语法错误");
        }
        return array_combine($matches[1], $matches[2]);
    }
    protected function parseApiFormDocBlock($block){
        $method = "";
        $path  = "";
        $des = '';
        $params = [];
        $tag = '';
        $return = ['ref' => null, 'des' => '', 'props' => []];
        $result = preg_match(sprintf("/%s%s%s%s%s/u",
        "@api\s+(?P<method>(get|post|put|patch|delete)),\s*",
        "(?P<path>[\s\S]+?),\s*(?P<tag>[\s\S]+?),\s*(?P<des>[\S\s]+?)\n+",
        "(?P<props>[\s\S]*)\n*\s*\*\s*",
        "@return\s+(?P<return_def>(#[\s\S]+?))\n+",
        "(?P<return_props>[^\n\/]*)\n*\s*"
        ), $block, $matches);
        if(!$result){
            throw new \Exception("api定义语法错误");
        }
        $method = $matches['method'];
        $path = $matches['path'];
        $des = $matches['des'];
        $tag = $matches['tag'];
        $params = $this->parsePropsFromDocBlock($matches['props']);
        list($returnRef, $returnDes) = $this->parseReturnFromDocBlock($matches['return_def']);
        $return['ref'] = $returnRef;
        $return['des'] = $returnDes;
        $return['props'] = $this->parsePropsFromDocBlock($matches['return_props']);
        return [
            'method' => $method,
            'path' => $path,
            'des' => $des,
            'tag' => $tag,
            'params' => $params,
            'return' => $return
        ];
    }
    protected function parseReturnFromDocBlock($docBlock){
        $def = preg_split('/\s*,\s*/', $docBlock, 2, PREG_SPLIT_NO_EMPTY);
        $def[0] = ltrim($def[0], '#');
        $def[1] = isset($def[1]) ? $def[1] : '';
        return $def;
    }
    protected function parseDefFromDocBlock($block){
        $defs = [];
        $result = preg_match_all('/\*\s*@def\s*#([a-zA-Z0-9\-\_\s]+)\n+\s*\*\s*([\s\S]*?)\n+\s*\*\s*[\n\/]/', $block, $matches);
        if(!$result){
            throw new \Exception("解析def出错");
        }
        foreach($matches[1] as $index => $defName){
            $props  = $this->parsePropsFromDocBlock($matches[2][$index]);
            $defs[] = [
                'name' => $defName,
                'props' => $props
            ];
        }
        return $defs;
    }
    protected function parsePropsFromDocBlock($propsDefs){
        $props = explode("\n", $propsDefs);
        foreach($props as $index => $propDef){
            $propDef = trim($propDef, "\n*-\t ");
            if(!$propDef){
                unset($props[$index]);
                continue;
            }
            $result = preg_match("/(?P<name>[a-z0-9A-Z\_\-\[\]]+)\s+(?P<prop_def>[^\n]+)/", $propDef, $vars);
            if(!$result){
                throw new \Exception("def的语法书写错误 " . $propDef);
            }
            $def = [
                'name' => $vars['name'],
                'type' => null,
                'ref' => null,
                'des' => null,
                'required' => null,
                'enum' => null,
                'query_or_path' => null
            ];
            $propdefs = preg_split('/\s*,\s*/', $vars['prop_def'], 2, PREG_SPLIT_NO_EMPTY);
            if($this->isRequiredProp($propdefs[0])){
                $def['required'] = $propdefs[0];
                $propdefs = preg_split('/\s*,\s*/', $propdefs[1], 2, PREG_SPLIT_NO_EMPTY);
            }
            if($this->isTypeProp($propdefs[0])){
                list($type, $ref) = $this->getRealType($propdefs[0]);
                if(!$type){
                    throw new \Exception(sprintf("def prop类型%s无效", $propdefs[0]));
                }
                $def['type'] = $type;
                $def['ref'] = $ref;
                $propdefs = preg_split('/\s*,\s*/', $propdefs[1], 2, PREG_SPLIT_NO_EMPTY);
            }
            if($this->isPathOrQueryProp($propdefs[0])){
                $def['query_or_path'] = $propdefs[0];
                $propdefs = preg_split('/\s*,\s*/', $propdefs[1], 2, PREG_SPLIT_NO_EMPTY);
            }
            if($this->isEnumProp($propdefs[0])){
                $def['enum'] = $propdefs[0];
                $propdefs = preg_split('/\s*,\s*/', $propdefs[1], 2, PREG_SPLIT_NO_EMPTY);
                $def['des'] = $propdefs[0];
            }else{
                $def['des'] = $propdefs[0];
            }
            $props[$index] = $def;
        }
        return $props;
    }
    protected function isEnumProp($str){
        return preg_match("/is_enum\([\s\S]+?\)/", $str);
    }
    protected function isRequiredProp($str){
        return in_array($str, ['optional', 'required']);
    }
    protected function isPathOrQueryProp($str){
        return in_array($str, ['in_query', 'in_path', 'in_body']);
    }
    protected function isTypeProp($str){
        $types = ['integer', 'boolean', 'string', 'mixed'];
        if(in_array($str, $types)){
            return true;
        }
        if(preg_match('/(object|array)#([a-zA-Z0-9\_]+)/', $str, $matches)){
            return true;
        }else{
            return false;
        }
    }
    protected function getRealType($type){
        $types = ['integer', 'boolean', 'string', 'mixed'];
        if(in_array($type, $types)){
            return [$type, null];
        }
        if(preg_match('/(object|array)#([a-zA-Z0-9\_]+)/', $type, $matches)){
            return [$matches[1], $matches[2]];
        }else{
            return [false, false];
        }
    }
    protected function getDocContentType($content){
        if(preg_match_all("/\* @def/", $content, $matches)){
            return "def";
        }elseif(preg_match_all("/\* @api/", $content, $matches)){
            return "api";
        }elseif(preg_match('/\* @root/', $content, $matches)){
            return "root";
        }
    }
    protected function prepareFiles($files = []){
        if(!$files){
            return [];
        }
        foreach($files as $index => $file){
            $path = Yii::getAlias($file);
            if(!file_exists($path)){
                throw new \Exception(sprintf("文件不存在%s", $file));
                return false;
            }
            $files[$index] = Yii::getAlias($file);
            file_put_contents($files[$index], str_replace("\r\n", "\n", file_get_contents($files[$index])));
        }
        return $files;
    }
    protected function getAlias(){
        return Yii::$app->params['apialias'];
    }
    protected function getFilesFromModule($module){
        return ArrayHelper::getValue(Yii::$app->params['apifiles'], $module, []);
    }




}
