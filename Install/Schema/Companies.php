<?php

namespace Apps\Tms\Packages\Companies\Install\Schema;

use Phalcon\Db\Column;
use Phalcon\Db\Index;

class Companies
{
    public function columns()
    {
        return
        [
           'columns' => [
                new Column(
                    'id',
                    [
                        'type'          => Column::TYPE_INTEGER,
                        'notNull'       => true,
                        'autoIncrement' => true,
                        'primary'       => true,
                    ]
                ),
                new Column(
                    'business_type',//Self Organisations, Customers, Vendors
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 20,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'reference',//Used for import from old system. Can be removed in the future.
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 100,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'logo',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 255,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'name',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 100,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'description',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 2048,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'company_phone_1',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 20,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'company_phone_2',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 20,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'company_fax',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 20,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'company_website',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 255,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'company_email',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 100,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'gst',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'gst_date',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 20,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'pan',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'pan_date',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 20,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'reg',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'reg_date',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 20,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'vendor_codes',
                    [
                        'type'          => Column::TYPE_JSON,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'payment_days',
                    [
                        'type'          => Column::TYPE_SMALLINTEGER,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'payment_terms',
                    [
                        'type'          => Column::TYPE_TEXT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'invoice_terms',
                    [
                        'type'          => Column::TYPE_TEXT,
                        'notNull'       => false,
                    ]
                ),
                new Column(//If there are invoice for this company, you cannot delete the company, just archive it.
                    'archived',
                    [
                        'type'          => Column::TYPE_BOOLEAN,
                        'notNull'       => true,
                    ]
                ),
                //Add Activity Logs
            ],
            'indexes' => [
                new Index(
                    'column_UNIQUE',
                    [
                        'name',
                        'business_type',
                        'reference',
                        'pan',
                    ],
                    'UNIQUE'
                )
            ],
            'options' => [
                'TABLE_COLLATION' => 'utf8mb4_general_ci'
            ]
        ];
    }

    public function indexes()
    {
        return
        [
            new Index(
                'column_INDEX',
                [
                    'name',
                    'business_type',
                    'reference',
                    'pan',
                    'archived'
                ],
                'INDEX'
            )
        ];
    }
}
