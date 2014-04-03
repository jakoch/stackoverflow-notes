<?php
$title = $_GET['title']; // <- you need to secure this
echo file_get_Contents('http://www.imdb.com/xml/find?json=1&nr=1&tt=on&q=' . $title);