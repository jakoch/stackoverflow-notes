<?php
// simple benchmark script with job support for multiple functions and "approaches", 
// which allows wrapping multiple approaches into anon functions, benchmarks them and allows to compare them
// Examplewise we compare strtr, str_replace, strpos and array key comparison with == and ===

$jobs = [
    'old/strtr' => function($args) {
        $results = [];
        foreach ($args as $key => $value) {
            $results[strtr($key, '_', '-')] = $value;           
        }
        return $results;
    },
    'new/str_replace' => function($args) {
        $results = [];
        foreach ($args as $key => $value) {
            $results[str_replace('_', '-', $key)] = $value;
        }
        return $results;
    },
    'laravel' => function($args) {
        $results = [];
        foreach ($args as $key => $value) {            
            $results[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;            
        }
        return $results;
    },
    'laravel/strtr' => function($args) {
        $results = [];
        foreach ($args as $key => $value) {            
            $results[strtr(ucwords(strtolower(strtr(substr($key, 5), '_', ' '))), ' ', '-')] = $value;            
        }
        return $results;
    },
    'strpos-prefix' => function($args) {
        $results = [];
        foreach ($args as $key => $value) { 
            if (strpos($key, 'HTTP_') === 0) {           
                $results[$key] = $value; 
            }           
        }
        return $results;
    },
    'string-compare-type-equals' => function($args) {
        $results = [];
        foreach ($args as $key => $value) {            
            if ($key === 'HTTP_ACCEPT_LANGUAGE' || $key === 'HTTP_ACCEPT_ENCODING' || $key === 'HTTP_HOST') {           
                $results[$key] = $value; 
            }           
        }
        return $results;
    },
    'string-compare-equals' => function($args) {
        $results = [];
        foreach ($args as $key => $value) {            
            if ($key == 'HTTP_ACCEPT_LANGUAGE' || $key == 'HTTP_ACCEPT_ENCODING' || $key == 'HTTP_HOST') {           
                $results[$key] = $value; 
            }            
        }
        return $results;
    },
];

class Benchmark
{
    public function __construct($jobs, $args, $iterations) 
    {
        $results = $this->runJobs($jobs, $args, $iterations);
        usort($results, function($a, $b) { 
            return $a['time'] > $b['time']; 
        });
        $newline = (PHP_SAPI=='cli'?PHP_EOL:'<br/>');
        echo 'PHP '.phpversion().$newline;
        echo 'Iterations: '.  $iterations.$newline;
        foreach ($results as $name => $res) {
            echo $res['name'].' :'.$newline;
            unset($res['name']);
            foreach ($res as $k => $v) {
                echo '    '.$k.': '.$v.$newline;
            }
        }
    }
    
    private function runJobs($jobs, $args, $iterations)
    {
        $res = array();
        foreach ($jobs as $name => $job) {
            $res[] = $this->bench($name, $job, $args, $iterations);
            echo '.';
            flush();
        }
        echo (PHP_SAPI=='cli'?PHP_EOL:'<br/>');        
        return $res;
    }
    private function bench($name, $job, $args, $iterations) 
    {
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $res = $job($args);
        }
        $end = microtime(true);
        // only 1 iteration output the result
        if($iterations === 1) {
            var_dump($name, $res);
        }
        if (!is_string($res)) {
            $res = serialize($res);
        }
        if (strlen($res) > 100) {
            $res = md5($res);
        }
        return array(
            'name' => $name,
            'time' => $end - $start,            
            'retval' => $res,
        );
    }
}

$args = [
    "HTTP_ACCEPT_LANGUAGE"     => "de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4",
    "HTTP_ACCEPT_ENCODING"     => "gzip, deflate, sdch",
    "HTTP_HOST"                => "localhost"
];

$iterations = 100000;

new Benchmark($jobs, $args, $iterations);