<?php

require_once "vendor/autoload.php";

use Laravie\Parser\Xml\Reader;
use Laravie\Parser\Xml\Document;
use Symfony\Component\Yaml\Yaml;
$config = Yaml::parseFile("conf.yaml");
$xml = (new Reader(new Document()))->load($config['base'].".xpml");
$basePerms = new SimpleXMLElement(file_get_contents($config['base'].".xpml"));

$errors = 0;

if (!empty($basePerms->permissions['extends']))
{
    $ext = $basePerms->permissions['extends'];
    $ext = str_replace(" ", '', $ext);
    $allExtends = explode(",",$ext);
    foreach ($allExtends as $extend)
    {
        if (file_exists($extend.".xpml"))
        {

            $temp_base = new SimpleXMLElement(file_get_contents($extend.".xpml"));
            foreach ($temp_base->permissions->perm as $permissioner)
            {
               $basePerms->permissions->addChild("perm", $permissioner)->addAttribute("perm",$permissioner['perm']);
            }
        } else {
            $errors++;
        }
    }
}
$src = Yaml::parseFile($config['byFile']);

$t = 0;
$l = "";
foreach ($src['permissions'] as $int => $permission)
{
    $l = $src['permissions'][$int];
    foreach ($basePerms->permissions->perm as $permissioner)
    {
        if (mb_strtolower($permission) == mb_strtolower($permissioner))
        {
            $src['permissions'][$int] = (string)$permissioner['perm'];
            $t = 1;
        } else {
            $src['permissions'][$int] = "UNKNOWN-".$l;
            $t = 0;
        }
        if ($t == 0) {
            continue;
        } else {
            $t = 0;
            $l = '';
            break;
        }
    }
}

$yaml = Yaml::dump($src);
file_put_contents($config['outFile'], $yaml);
echo "<br/><br/>";
if (file_exists($config['outFile']))
{
    echo "<strong style='color:green;'>Successful</strong><br>";
    echo "<strong style='color:darkred;'>Ошибок: $errors</strong>";
} else {
    echo "<strong style='color:red;'>Unexpected error</strong>";
}