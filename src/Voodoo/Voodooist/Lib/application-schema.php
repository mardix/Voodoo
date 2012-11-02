<?php

$ApplicationSchema = array(
    array(
        "name"      => "www",
        "modules"   => array(
            array(
                "name"          => "Main",
                "template"      => "Default",
                "isApi"         => false,
                "omitViews"     => true,         

                "controllers"    => array(
                    array(
                        "name"      => "Index",
                        "actions"   => array("index")
                    ),

                ),

                "models"         => array(
                    array(
                        "name"          => "Beats",
                        "dbAlias"       => "Beats",
                        "table"         => "beatbox",
                        "primaryKey"   => "id",
                        "foreignKey"   => "%_id"
                    ),
                    array(
                        "name"          => "Beats/Producer",
                        "dbAlias"       => "Members",
                        "table"         => "account",
                        "primaryKey"   => "id",
                        "foreignKey"   => "id_members"
                    )

                ),

            ),


        )
    ),

);
