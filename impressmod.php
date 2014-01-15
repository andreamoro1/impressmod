<?php

/******
*
* impressmod.php
* by Andrea Moro, Jan 2014 impressmod at andreamoro.net
* 
* modifies some div attributes in html file containing impress.js slides
* refer to impress.js for details
*
******/

include('simple_html_dom.php');

$shortopts = "fhx:y:z:s:";
$longoptions = array('x:','y:','z:','rx:','ry:','rz:'
                    ,'s:','fm','steps:','help','f:','o:');
$options = getopt($shortopts,$longoptions);


// some help stuff
if (isset($options['h'])) {
  echo "
Options:

 --f: input filename
 --o: output filename (default: =input filename)
 
 --x: data-x
 --y: data-y
 --z: data-z
 --rx: data-rotate-x
 --ry: data-rotate-y
 --rz: data-rotate-z
 --s: scale 
 
 Option values:
	  +n,-n (add and subtract n from value)
	  =n (substitute value with n)
	  dn (add n*x from value - to increase distance between steps)
 
 --fm: force substitution also if value is missing (default: false)
 --steps = 'string': string with step values (comma separated, hyphen for ranges)
      (default: all slides)
 
Examples

php impressmod.php --f input.html --steps 3-5 --x -20 --y =200
   decrease data-x by 20 and set data-y to 200 on slides 3,4,5 of input.html 
   and overwrite the file
php impressmod.php --f input.html
   list available slides and their id, if present
php impressmod.php --f input.html --output output.html --steps 2,3,8-9 --x d20 --fm
   increase the data-x gap between slides 2 and 3, 3 and 8, and 
   8 and 9 by 20. Slides 2 remains unchanged. Data-x will be set 
   wherever missing
php impressmod.php --f input.html --steps 5-2,overview,8
   process slides in the following order: 5,4,3,2,[id=overview],5,4
  
 
";
	exit;
} 



// let's start with the whole enchilada. Get first the file to parse
$inputfile =  $options['f'];

if (!isset($options['o'])) { 
	$outputfile = $inputfile; 
} else {
	$outputfile = $options['o'];
}
echo "\nInput file: $inputfile";

$html = new simple_html_dom();

$html -> load_file($inputfile);

$steps = $html -> find('div.step');
$num_steps = sizeof($steps);

echo "\nTotal slides: " . $num_steps . "\n";

// find the slides needed to be processed
if (!isset($options['steps'])) $options['steps'] = '1-';

$slideranges = split(',',$options['steps']);
$slides = array();
$slidesnotfound = array();

foreach ($slideranges as $slide) {
  	if (preg_match('/([0-9]*)-([0-9]*)/',$slide,$matches)) {
    		if ($matches[2]=='') $matches[2] = $num_steps;
    		if ($matches[1]=='') $matches[1] = 1;  
    		if ($matches[1]<1) $matches[1]==1;
    		if ($matches[2]<1) $matches[2]==1; 	
    		if (max($matches[2],$matches[1]) > $num_steps) {
    			$slidesnotfound = 
    				array_merge($slidesnotfound,
    										range($num_steps+1,max($matches[2],$matches[1])));    		
    		}
				if ($matches[2]>$num_steps) $matches[2]=$num_steps;
    		if ($matches[1]>$num_steps) $matches[1]=$num_steps;
    	
 		   	$slides = array_merge($slides,range($matches[1],$matches[2]));
  	} else { // not a range
  	  	$slide = $slide+0;
    		if (is_numeric($slide)) {
  	   			if ($slide>0 & $slide <= $num_steps) { 
								$slides[] = $slide; 
						} else {
								$slidesnotfound[] = $slide;
						}	
  			} else { // a string: look for slide with this id
  					for ($i=0;$i < $num_steps; $i++) {
  							if ($steps[$i]->id == $slide) {
		    						$slides[] = $i+1; 
		    						break;
		    				}
								if ($i== $num_steps - 1) $slidesnotfound[] = $slide;
						}
				}
  	} 
}

echo 'Slides to process: ' . implode(',',$slides) . "\n";
if (sizeof($slidesnotfound)>0) echo 'Slides not found: ' . implode(',',$slidesnotfound) . "\n";
echo "\n";

var_dump($options);
// find the attributes to modify
$attributes = array();
foreach ($options as $optionkey=>$optionvalue) { 

  $optionkey_first = substr($optionkey,0,1);
	if ($optionkey == 's') {
		$attributes['data-scale'] = $optionvalue;
	} elseif ($optionkey_first == 'r' ) {
		$attributes['data-rotate-'.substr($optionkey,1,1)] = $optionvalue;
	} elseif ($optionkey_first == 'x' | $optionkey_first== 'y' | $optionkey_first=='z') { 
		$attributes['data-'. $optionkey_first] = $optionvalue;
	} else {
		continue;
	}
}

var_dump($attributes);

// now start processing slides 
foreach ($slides as $key=>$slide) {
	$step = $steps[$slide-1];
	
	if (isset($step->id)) $id = '(#'.$step->id.')';
	echo "Slide $slide $id: \n";	    
	unset($id);

	foreach ($attributes as $attribute=>$optionvalue) {
		
		$value = $step->attr[$attribute];
		
		// if option is not set in file and does not want to force it, continue
		if (!isset($options['fm']) & !isset($value)) continue;
		
		echo "  ".$attribute.': '.$value;
		
		
		if (substr($optionvalue,0,1) == '=') {
			$step->attr[$attribute] = substr($optionvalue,1);  
		} elseif (substr($optionvalue,0,1) == 'd') {
			$step->attr[$attribute] = $value + $key * substr($optionvalue,1);  
		} else {
			$step->attr[$attribute] = $value + $optionvalue;
		}  
	  
		echo ' -> ' . $step -> attr[$attribute] . "\n";
  }
}

$fp = fopen($inputfile,'r');
$fp2 = fopen($inputfile.'.bak','w');
$oldfile = fread($fp,filesize($inputfile));
fwrite($fp2,$oldfile);
fclose($fp);
fclose($fp2);

$fp3 = fopen($outputfile,'w');
fwrite($fp3,$html);
fclose($fp3);

if (sizeof($attributes) >=1) echo "\nOutput file: $outputfile (backup $inputfile.bak) \n";

echo "\n";

?>