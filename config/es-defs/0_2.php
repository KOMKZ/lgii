<?php
/*

insert into truc_country value (null, '10000001', '亚洲', '', 0);
insert into truc_country value (null, '10000002', '北美洲', '', 0);
insert into truc_country value (null, '10000003', '南美洲', '', 0);
insert into truc_country value (null, '10000004', '非洲', '', 0);
insert into truc_country value (null, '10000005', '大洋洲', '', 0);
insert into truc_country value (null, '10000006', '欧洲', '', 0);
insert into truc_country value (null, '10000007', '阿联酋', '', 0);
insert into truc_country value (null, '10000008', '波斯尼亚和黑塞哥维那', '', 0);
insert into truc_country value (null, '10000009', '克罗地亚', '', 0);
insert into truc_country value (null, '10000010', '马恩岛', '', 0);
insert into truc_country value (null, '10000011', '马约特', '', 0);
insert into truc_country value (null, '10000012', '吉尔吉斯斯坦', '', 0);
insert into truc_country value (null, '10000013', '留尼汪岛', '', 0);
insert into truc_country value (null, '10000014', '瓜德罗普', '', 0);
insert into truc_country value (null, '10000015', '法属圣马丁', '', 0);
insert into truc_country value (null, '10000016', '巴勒斯坦', '', 0);
insert into truc_country value (null, '10000017', '塞尔维亚', '', 0);
insert into truc_country value (null, '10000018', '马其顿', '', 0);
insert into truc_country value (null, '10000019', '泽西岛', '', 0);
insert into truc_country value (null, '10000020', '克罗地亚', '', 0);




 */



return [
    'index' => 'blog',
    'body' => [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'analysis' => [
                'char_filter' => [],
                'analyzer' => [
                    'myik' => [
                        'type' => 'custom',
                        'tokenizer' => 'ik_smart',
                        'filter' => ["area_synonym", "my_stop"]
                    ],
                    'school' => [
                        'type' => 'custom',
                        'tokenizer' => 'ik_smart',
                        'filter' => ['school_synonym']
                    ]
                ],
                'tokenizer' => [],
                'filter' => [
                    'area_synonym' => [
                        'type' => "synonym",
                        'synonyms_path' => '/home/master/soft/target/elasticsearch-2.4.3/plugins/ik/config/synonym.txt'
                    ],
                    'school_synonym' => [
                        'type' => "synonym",
                        'synonyms_path' => '/home/master/soft/target/elasticsearch-2.4.3/plugins/ik/config/school_area_synonym.txt'
                    ],
                    'my_stop' => [
                        'type' => 'stop',
                        'stopwords_path' => "/home/master/soft/target/elasticsearch-2.4.3/plugins/ik/config/custom/ext_stopword_strict.dic"
                    ]
                ]
            ]

        ]
    ],
    'mapping' => [
        'name' => 'post',
        'properties' => [
            'created_at' => [
                'type' => 'integer',
                'index' => 'not_analyzed'
            ],
            'id' => [
                'type' => 'integer',
                'index' => 'not_analyzed'
            ],
            'create_uname' => [
                'type' => 'string',
                'analyzer' => 'standard'
            ],
            'title' => [
                'type' => 'string',
                'analyzer' => 'standard'
            ],
            'content' => [
                'type' => 'string',
                'analyzer' => 'standard'
            ]
        ]
    ],
    'query' => function($text){
        return [
            'filtered' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            'multi_match' => [
                                'query' => $text,
                                'type' => 'most_fields',
                                'fields' => [
                                    'title',
                                    'content',
                                    'create_uname'
                                ],
                                'minimum_should_match' => "10%",
                            ]
                        ],
                    ]
                ]
            ]
        ];
    },
    'data' => [
        ['中国人', '章天佑', '中国人是很厉害的', time()],
    ]
];







