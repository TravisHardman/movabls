<?php
/*
$content = array(
    'mytag' => array(
        'movabl_GUID' => 'NESTED_MEDIA',
        'movabl_type' => 'media',
        'tags' => array(
            'footag' => array(
                'movabl_GUID' => 'FOO_MEDIA',
                'movabl_type' => 'media'
            )
        )
    ),
    'othertag' => array(
        'toplevel_tag' => 'mytag'
    ),
    'functiontag' => array(
        'movabl_GUID' => 'NESTED_FUNCTION',
        'movabl_type' => 'function',
        'tags' =>array(
            'tentimes' => array(
                'movabl_GUID' => 'FOO_MEDIA',
                'movabl_type' => 'media'
            )
        )
    ),
    'placetag' => array(
        'movabl_GUID' => 'NESTED_PLACE',
        'movabl_type' => 'place'
    ),
    'expressiontag' => array(
        'expression' => '$GLOBALS->_SERVER["REQUEST_URI"]'
    ),
    'otherexpressiontag' => array(
        'expression' => '(rand(0,10) > 5 ? "HIGH NUMBER!!!" : "LOW NUMBER!!!")'
    ),
    'finalexpressiontag' => array(
        'expression' => '3 + 5 + 75'
    ),
    'phptag' => array(
        'php' => 'date',
        'interface_GUID' => 'MY_SUPER_INTERFACE'
    )
);
// */


/*
$content = array(
    'tripletag' => array(
        'movabl_GUID' => 'FOO_MEDIA',
        'movabl_type' => 'media'
        )
    );
// */

/*
$content = array(
    "hello dogtastic"
);
// */

//*
$content = array(
    'format' => array(
        'expression' => '"m/d/Y"'
    ),
    'time' => array(
        'expression' => 'time()-86400'
    )
);
// */

echo json_encode($content);
?>
