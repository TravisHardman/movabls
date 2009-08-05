<?php
//*
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
    )
);
// */


//*
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

echo json_encode($content);
?>
