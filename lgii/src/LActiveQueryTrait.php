<?php
namespace lgii;

use Yii;

trait LActiveQueryTrait{
    /**
     * @var array 缩写名称集合
     */
    static protected $shortCutNames = [];

    /**
     * @var array
     */
    static protected $relations = [];


    public static function getFullInfo($selectFields){
        $tableName = static::tableName();
        $tableNameClean = static::formatTableName($tableName);
        $query = static::find();

        $alia = static::geneUniqueShortCutName($tableNameClean);
        $query->from([
            $alia => $tableName,
        ]);

        list($fields, $withParams) = static::buildDynamicWithParam($selectFields);
        list($fields, $leftJoinTables) = static::appendAliaToField($fields, $tableNameClean);
        static::appendLeftJoinToQuery($query, $leftJoinTables);
        $query->select($fields)
            ->with($withParams)
            ->asArray();
        return $query;
    }

    /**
     * 计算字符串唯一缩写
     * @param string $name 需要生成缩写的名称
     * @return string 返回唯一缩写
     */
    protected static function geneUniqueShortCutName($name){
        if(isset(static::$shortCutNames[$name])){
            return static::$shortCutNames[$name];
        }
        $cut = 1;
        $alias = static::generateShortCutName($name, $cut);
        while(isset(static::$shortCutNames[$name]) &&
            static::$shortCutNames[$name] == $alias
        ){
            $cut++;
            $alias = static::generateShortCutName($name, $cut);
        }
        static::$shortCutNames[$name] = $alias;
        return $alias;
    }

    /**
     * 计算字符串缩写
     * @param string $name 需要生成缩写的名称
     * @param int $cut 偏移的位置
     * @return string 返回缩写
     */
    protected static function generateShortCutName($name, $cut = 1){
        $parts = explode('_', $name);
        $cans = [];
        foreach($parts as $value){
            $cans[] = substr($value, 0, $cut);
        }
        return $cut > 1 ? implode('_', $cans) : implode('', $cans);
    }

    /**
     * 构建联表语句
     * @return array
     */
    protected static function generateRelationOnCondtion(){
        if(static::$relations){
            return static::$relations;
        }
        $relations = [];
        $db = Yii::$app->db;
        foreach ($db->getSchema()->getTableSchemas('') as $table) {
            foreach ($table->foreignKeys as $refs) {
                $refTable = $refs[0];
                $refTableSchema = $db->getTableSchema($refTable);
                if ($refTableSchema === null) {
                    continue;
                }
                unset($refs[0]);
                $refAlia = static::geneUniqueShortCutName($refTable);
                $priAlia = static::geneUniqueShortCutName($table->fullName);
                $ons = [];
                $fks = array_keys($refs);
                foreach($refs as $key => $name){
                    unset($refs[$key]);
                    $ons[] = sprintf(" %s.%s = %s.%s ",
                        $priAlia, $key,
                        $refAlia, $name
                    );
                }
                $relations[$table->fullName][$refTable] = [
                    $refTable . " as " . $refAlia,
                    implode('and', $ons),
                    $refAlia,
                    false
                ];
                $isMulit = static::isHasManyRelation($table, $fks);
                $relations[$refTable][$table->fullName] = [
                    $table->fullName . " as " . $priAlia,
                    implode('and', $ons),
                    $priAlia,
                    $isMulit
                ];
            }
        }
        return static::$relations = $relations;
    }

    protected function getDbConnection(){
        return Yii::$app->get('db', false);
    }


    /**
     * 检查表和外表是否是一对多关系
     * @param string $table
     * @param array $fks
     * @return bool
     */
    protected static function isHasManyRelation($table, $fks)
    {
        $uniqueKeys = [$table->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, static::getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch (\NotSupportedException $e) {
            // ignore
        }
        foreach ($uniqueKeys as $uniqueKey) {
            if (count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * 分类字段得到一级字段和二级字段
     * @param array $fields
     * @return array
     */
    protected static function parseFields($fields){
        $parFields = [];
        $subFields = [];
        foreach($fields as $name){
            if(false === $pos = strpos($name, '.')){
                $parFields[] = $name;
                continue;
            }

            $subFields[substr($name, 0, $pos)][] = substr($name, $pos + 1);
        }
        return [$parFields, $subFields];
    }

    /**
     * 对字段加入表别名
     * @param array $fields
     * @param string $tableNameClean
     * @return array
     */
    protected static function appendAliaToField($fields, $tableNameClean){
        $alia = static::geneUniqueShortCutName($tableNameClean);
        $leftJoinTables = [];
        foreach($fields as $index => $name){
            if(empty($leftJoinTables[$tableNameClean])){
                $leftJoinTables[$tableNameClean] = [];
            }
            if(false !== strpos($name, '[')){
                $fields[$index] =  str_replace('[', '',
                    str_replace(']', '.', $name)
                );
                list($a, ) = explode('.', $fields[$index]);
                if(!in_array($a, $leftJoinTables[$tableNameClean])){
                    $leftJoinTables[$tableNameClean][] = $a;
                }
            }else{
                $fields[$index] = sprintf("%s.%s", $alia, $name);
            }
        }
        return [$fields, $leftJoinTables];
    }


    /**
     * 去除不必要的表名称信息
     * @param string $tableName 表名称
     * {{%user}} => user
     * @return string 返回格式化的的表名称
     */
    protected static function formatTableName($tableName){
        return str_replace(['{', '}', '%'], '', $tableName);
    }

    /**
     * 对查询对象加入联表信息
     * @param \yii\db\ActiveQuery $query 查询对象
     * @param array $leftJoinTables 联表数据
     * e.g.
     * ```php
     * [
     *   'user' => ['ud', 'ui] // 代表user表需要联表ud,ui表(别名)
     * ]
     * ```
     */
    protected static function appendLeftJoinToQuery($query, $leftJoinTables){
        $relations = static::generateRelationOnCondtion();

        foreach($leftJoinTables as $table => $relateAlias){
            $targetRelations = $relations[$table];
//            if(isset($leftJoinTables['user_skills'])){
//                // 就是这里没索引没什么好说的了
//            }
            foreach($relateAlias as $alia){
                foreach($targetRelations as $refTable => $refs){
                    if(false !== $refs[3] || $refs[2] != $alia){
                        continue;
                    }
                    $query->leftJoin($refs[0], $refs[1]);
                }
            }
        }
    }

    /**
     * 根据字段定义动态构建with数据
     * @param array $fields 需要构建的字段数据
     * e.g.
     * ```php
     * [
     *  'u_id",
     *  'u_name',
     *  '[ud]ud_id',
     *  '[ud]ud_long_intro',
     *  'user_skills.us_id',
     *  'user_skills.u_id',
     *  'user_skills.sk_id',
     *  'user_skills.[s]sk_name',
     *  'user_skills.sk.sk_id',
     *  'user_skills.sk.sk_name',
     *  'user_skills.sk.skill_tags.skt_id',
     *  'user_skills.sk.skill_tags.sk_id',
     *  'user_skills.sk.skill_tags.skt_name',
     * ]
     * ```
     * [ud]ud_id 表示获取的是别名表ud的数据字段ud_id
     * u_id 表示获取的是主表的数据u_id,主表的数据是什么这个不确定
     * user_skills.us_id 表示获取主表的关联数据user_skills,指定字段us_id
     * 'user_skills.[s]sk_name' 表示获取关联数据user_skills,s代表连表
     *
     * @return array 返回1对1字段还有with数据
     * 数组的第一个元素是字段列表，每个字段会被加入别名，如
     * [ud]ud_id会被变成ud.ud_id
     * 数组的第二个元素是 with参数,参考with方法了解详细
     *
     */
    protected static function buildDynamicWithParam($fields){
        $with = [];
        list($parFields, $subFields) = static::parseFields($fields);

        foreach($subFields as $relationName => $relationFields){
            list($buildedFields, $buildedWithParam) = static::buildDynamicWithParam($relationFields);

            $with[$relationName] =  call_user_func_array(function($params){
                return function($query) use ($params){
                    $modelClass = $query->modelClass;
                    $tableName = static::formatTableName($modelClass::tableName());
                    $alia = static::geneUniqueShortCutName($tableName);
                    $query->from([
                        $alia => $tableName
                    ]);
                    $leftJoinTables = [];

                    if(!empty($params['fields'])){

                        list($fields, $leftJoinTables) = static::appendAliaToField($params['fields'], $tableName);

                        $query->select($fields);
                    }
                    if(!empty($params['with'])){
                        $query->with($params['with']);
                    }
                    static::appendLeftJoinToQuery($query, $leftJoinTables);
                };
            }, [[
                'fields' => $buildedFields,
                'with' => $buildedWithParam,
            ]]);
        }
        return [$parFields, $with];
    }
}
