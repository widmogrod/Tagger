<?php
/**
 * @author gabriel
 */

function htmltrim($string)
{
    $pattern = '(?:[ \t\n\r\x0B\x00\x{A0}\x{AD}\x{2000}-\x{200F}\x{201F}\x{202F}\x{3000}\x{FEFF}]|&nbsp;|<br\s*\/?>)+';
    return preg_replace('/^' . $pattern . '|' . $pattern . '$/u', '', $string);
}

$html = file_get_contents(__DIR__ . '/_data/laksa.html');
//echo $content;
// opcionalne wykrywanie kodowania
$html = htmltrim($html);
$html = html_entity_decode($html, null, 'UTF-8');
$html = preg_replace('(<(style|STYLE)[^>]*>[^<]*(</style>|</STYLE>))', '', $html);
$html = preg_replace('(<(script|SCRIPT)[^>]*>[^<]*(</script>|</SCRIPT>))', '', $html);
//$html = preg_replace('([\t]+)', ' ', $html);

$html = strip_tags($html);
//echo $html;

$lines = explode("\n", $html);
//$lines = array_map('htmltrim', $lines);
$lines = array_map('trim', $lines);
$lines = array_filter($lines);
//var_dump($lines);

$dots = array();
while ($line = array_shift($lines))
{
    $dot = explode('.', $line);
    $dots = array_merge($dots, $dot);
}

$dots = array_map('trim', $dots);
$dots = array_filter($dots);
//var_dump($dots);

//

$comas = array();
while ($dot = array_shift($dots))
{
    $coma = explode(',', $dot);
    $comas = array_merge($comas, $coma);
}

$comas = array_map('trim', $comas);
//var_dump($comas);

$comas = array_filter($comas, function($value){
    return preg_replace('([^\p{L}]p{N}]+)', null, $value);
});
//var_dump($comas);

$words = array();
while ($coma = array_shift($comas))
{
    $word = explode(' ', $coma);
    $words = array_merge($words, $word);
}
//var_dump($words);

$words = array_filter($words, function($value){
    return (mb_strlen($value, 'utf-8') >= 5);
});

$words = array_map(function($value){
    $value = mb_strtolower($value, 'utf-8');
    $value = preg_replace('([^\p{L}]p{N}\p{Mc}]+)', ' ', $value);
    return $value;
}, $words);

// zerowanie kluczy
$words = array_values($words);

$summary = array_count_values($words);
asort($summary, SORT_STRING);

//var_dump($summary);


$pairs = array();
$pairsInline = array();
$summary = $aggWords = array_keys($summary);

// od wyrazów z najwiekszym priorytetem
while ($word = array_pop($summary))
{
    if (!isset($pairs[$word])) {
        $pairs[$word] = array();
    }

    $wordsPositions = array_intersect($words, array($word));
    $wordsPositions = array_keys($wordsPositions);

    while (null !== ($pos = array_shift($wordsPositions)))
    {
        if (array_key_exists($pos+1, $words))
        {
            $nextWord = $words[$pos+1];
            $pairs[$word][]=  $nextWord;
            $pairsInline[] = $word .' '. $nextWord;
        }
    }
}

//var_dump($pairsInline);
$summary = array_count_values($pairsInline);
asort($summary, SORT_STRING);
var_dump($summary);


$combained = array();
$combainedInline = array();


$summary = $aggWords;
while ($word = array_pop($summary))
{
    if (array_key_exists($word, $pairs))
    {
        foreach($pairs[$word] as $nextWord)
        {
            $wordsPositions = array_intersect($words, array($nextWord));
            $wordsPositions = array_keys($wordsPositions);

            while (null !== ($pos = array_shift($wordsPositions)))
            {
                if (array_key_exists($pos+1, $words))
                {
                    $nextWord2 = $words[$pos+1];
                    $combainedInline[] = $word .' '. $nextWord .' '. $nextWord2;
                }
            }
        }
    }
}
//var_dump($combainedInline);

$summary = array_count_values($combainedInline);
asort($summary, SORT_STRING);
//var_dump($summary);