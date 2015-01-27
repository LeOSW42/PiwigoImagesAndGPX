<?php // Prepare list of photos

define('PHPWG_ROOT_PATH','./');
include_once( PHPWG_ROOT_PATH.'include/common.inc.php' );

if(isset($_GET['catID'])) $catID = $_GET['catID'];
else $catID = 0;

$LIMIT_SEARCH = "FIND_IN_SET(".$catID.", c.uppercats) AND ";
$INNER_JOIN = "INNER JOIN ".CATEGORIES_TABLE." AS c ON ic.category_id = c.id";

$forbidden = get_sql_condition_FandF(
array
(
    'forbidden_categories' => 'ic.category_id',
    'visible_categories' => 'ic.category_id',
    'visible_images' => 'i.id'
),
"\n AND"
);

    $query="SELECT i.latitude, i.longitude,
    IFNULL(i.name, '') AS `name`,
    IF(i.representative_ext IS NULL,
        CONCAT(SUBSTRING_INDEX(TRIM(LEADING '.' FROM i.path), '.', 1 ), '-sq.', SUBSTRING_INDEX(TRIM(LEADING '.' FROM i.path), '.', -1 )),
        TRIM(LEADING '.' FROM
            REPLACE(i.path, TRIM(TRAILING '.' FROM SUBSTRING_INDEX(i.path, '/', -1 )),
                CONCAT('pwg_representative/',
                    CONCAT(
                        TRIM(TRAILING '.' FROM SUBSTRING_INDEX( SUBSTRING_INDEX(i.path, '/', -1 ) , '.', 1 )),
                        CONCAT('-sq.', i.representative_ext)
                    )
                )
            )
        )
    ) AS `pathurl`,
    TRIM(TRAILING '/' FROM CONCAT( i.id, '/category/', IFNULL(i.storage_category_id, '') ) ) AS `imgurl`,
    IFNULL(i.comment, '') AS `comment`,
    IFNULL(i.author, '') AS `author`,
    i.width
        FROM ".IMAGES_TABLE." AS i
            INNER JOIN (".IMAGE_CATEGORY_TABLE." AS ic ".$INNER_JOIN.") ON i.id = ic.image_id
            WHERE ".$LIMIT_SEARCH." i.latitude IS NOT NULL AND i.longitude IS NOT NULL ".$forbidden." GROUP BY i.id;";

    $php_data = array_from_query($query);
    //print_r($php_data);
    $js_data = array();
    foreach($php_data as $array)
    {
        // MySQL did all the job
        //print_r($array);
        $js_data[] = array((double)$array['latitude'],
                   (double)$array['longitude'],
                   $array['name'],
                   get_absolute_root_url() ."i.php?".$array['pathurl'],
                   get_absolute_root_url() ."picture.php?/".$array['imgurl'],
                   get_absolute_root_url() ."i.php?".str_replace('sq','xs',$array['pathurl']),
                   $array['comment'],
                   $array['author'],
                   (int)$array['width']
                   );
    }

    $js = $_GET['callback'].'('.str_replace("\/","/",json_encode($js_data)).')';

echo $js;
?>
