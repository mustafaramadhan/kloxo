<?php

include_once 'lib/html/include.php';

function printUsage()
{
    echo 'Usage: reflect --type=add --parent-class= --parent-name= --class= [--v-var= --v-val=]
Usage: reflect --type=update --subaction= --class= --name=

Example: How to add a customer to admin:
    /script/reflect --type=add --parent-class=client --parent-name=admin --class=client --v-var=ttype --v-val=customer

Example: How to add a wordpress to a domain domain.com:
    /script/reflect --type=add --parent-class=web --parent-name=domain.com --class=easyinstaller --v-var=appname --v-val=wordpress
';
}

function getModeFromType($type)
{
    switch($type)
    {
        case 'action':
            $istr = "__acdesc_";
        break;
        case 'property':
            $istr = "__desc_";
        break;
        case 'type': # [TODO] Check this, temporal workaround
            $istr = "__acdesc_";
        break;
        default:
            $istr = "__acdesc_";
        break;
    }
    return $istr;
}

function printProperty($class, $type)
{
    $r = new ReflectionClass($class);
    foreach($r->getProperties() as $s)
    {
        $istr = getModeFromType($type);
        if(!csb($s->name, $istr)) continue;
        $descr = get_classvar_description($class, $s->name);
        $name = strfrom($s->name, $istr);
        if (csa($descr[0], "q")) continue;
        if (cse($name, "_f")) continue;
        if (cse($name, "_l")) continue;
        if (cse($name, "_o")) continue;
        printf("%35s %s\n", $name, $descr['help']);
    }
}

initProgram('admin');
init_language();

if(!isset($argv[2]))
{
    printUsage();
    exit;
}

$list = parse_opt($argv);
$class = $list['class'];
$type = $list['type'];

switch($type)
{
    case 'update':
        $subaction = $list['subaction'];
        $object = new $class(null, null, $list['name']);
        $object->get();
        if ($object->dbaction === 'add')
        {
            print("object {$object->getClName()} doesn't exist\n");
            exit;
        }
        $param = $object->updateform($subaction, $param);
    break;
    case 'add':
        $pc = $list['parent-class'];
        $pn = $list['parent-name'];
        $parent = new $pc(null, null, $pn);
        $parent->get();
        if ($parent->dbaction === 'add')
        {
            print("parent {$parent->getClName()} doesn't exist\n");
            exit;
        }
        $typtd = null;
        if (isset($list['v-val'])) $typtd['val'] = $list['v-val'];
        if (isset($list['v-var'])) $typtd['var'] = $list['v-var'];
        $parent->priv = new Priv(null, null, $pn);
        $param = exec_class_method($class, 'addform', $parent, $class, $typtd);
        $param = $param['variable'];
    break;
    default:
        printProperty($class, $type);
    break;
}

foreach($param as $k => $v)
{
    if(csb($k, "__v") || csb($k, "__c") || csb($k, "__m")) continue;
    $desc = get_classvar_description($class, $k);
    $c = null;
    $prep = null;
    if($v && is_array($v) && $v[0] === 'M') $c = "Static: {$v[1]}";
    if(csa($desc[0], 'q')) $k = "priv-$k";

    printf("%45s %s $c\n", $k, $desc[2]);
}