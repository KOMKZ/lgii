<?php
return [
    'index' => 'blog',
    'body' => [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'char_filter' => [],
            'analyzer' => [],
            'tokenizer' => [],
            'filter' => []
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
                                'type' => 'best_fields',
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
        ['中', '国', '人', time()],
        ['中国人', '', '', time()],
    ]
];