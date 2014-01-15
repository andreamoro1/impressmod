<?php

/**************************************************************************************
*
* impressmod.php
* by Andrea Moro, Jan 2014 impressmod at andreamoro.net
* 
* modifies some div attributes in html file containing impress.js slides
* refer to impress.js for details
*
**************************************************************************************/

include('simple_html_dom.php');

$shortopts = "hv";
$longoptions = array('x:','y:','z:','rx:','ry:','rz:'
                    ,'s:','fm','steps:','help','f:','o:'
                    ,'restore');
$options = getopt($shortopts,$longoptions);

if (sizeof($options)==0) $options['h']=1;

// some help stuff and version
if (isset($options['v'])) {
	echo "
impressmod.php version 0.231

type php impressmod.php --h for help
";
	exit;
}

if (isset($options['h']) | isset($options['help']) ) {
  echo "
NAME 
	impressmod.php php script to modify impress.js slides

SYNOPSIS
    php impressmod.php 	[--f <file>] [--o <file>] [--x <value>] [--y <value>] [--z <value>]
    					[--rx <value>] [--ry <value>] [--rz <value>>] [--s <value>] [--fm]
    					[--steps <string>] [-h]

DESCRIPTION
	modifies location attributes of html slides created for impressmod.js
	requires php with simple_html_dom.php library

OPTIONS

	--f <file>
	  input filename (required)
	  
	--o <file>  
	  output filename (default: =input filename)
 
	--x <value>
	  data-x attribute change
	  <value>:
	    +n,-n (add and subtract n from value)
	    =n (substitute value with n)
	    dn (add n*x from value - to increase distance between steps

	--y <value>
	  data-y attribute change, <value> see --x
	--z <value>
	  data-z attribute change, <value> see --x
	--rx <value>
	  data-rotate-x attribute change, <value> see --x
	--ry <value>
	  data-rotate-y attribute change, <value> see --x
	--rz <value>
	  data-rotate-z attribute change, <value> see --x
	--s <value>
	  scale attribute change, <value> see --x
 

	--fm
	  force substitution when attribute is missing (default: false)
    
    --steps <string> 
      slides to modify (default: all steps)
	  <string>
	  	comma-separated string of integers of step number, 
	  	hyphen-separate ranges, and step #ids
	  	
	--restore --f <inputfile>
	  restores <inputfile>.bak to <inputfile> 
	  	
	--help 
	  this help file
	  
	--version
	  prints version number
	  
EXAMPLES

1) php impressmod.php --f input.html --steps 3-5,7 --x -20 --y =200
	decrease data-x by 20 and set data-y to 200 on slides 3,4,5,7 of input.html 
	and overwrite the file 
	does not modify attribute on slides where  they are missing

2) php impressmod.php --f input.html
	list available slides and their id, if present

3) php impressmod.php --f input.html --output output.html --steps 2,3,8-9 --x d20 --fm
	increase the data-x gap between slides 2 and 3, 3 and 8, and 8 and 9 by 20. 
	Slides 2 remains unchanged. Data-x will be created wherever missing

4) php impressmod.php --f input.html --steps 5-2,overview,8
	process slides in the following order: 5,4,3,2,[id=overview],8
	
AUTHORS
	andrea moro imprssmod@andreamoro.net 1/2014
	
LICENSE
	GPL

";
	exit;
} 


// let's start with the whole enchilada. Get first the file to parse
$inputfile =  $options['f'];

if (isset($options['restore'])) {
    if (!isset($options['f'])) {
    	echo "\n Please specify a filename to restore with --f <filename> (without .bak)\n";
    } else {
		$fp2 = fopen($inputfile.'.bak','r');
       	$fp = fopen($inputfile,'w');
		$oldfile = fread($fp2,filesize($inputfile.'.bak'));
		fwrite($fp,$oldfile);
		fclose($fp);
		fclose($fp2);
		echo "\n $inputfile restored from $inputfile.bak \n";
	}
	exit;    	
}

if (!isset($options['o'])) { 
	$outputfile = $inputfile; 
} else {
	$outputfile = $options['o'];
}
echo "\nInput file: $inputfile";

// get the dom from input file
$html = new simple_html_dom();
$html -> load_file($inputfile);

// we only need the step divs, so let's get them
$steps = $html -> find('div.step');
$num_steps = sizeof($steps);
echo "\nTotal slides: " . $num_steps . "\n";

// find the slides needed to be processed
// default is to process all slides
if (!isset($options['steps'])) $options['steps'] = '1-';

// split requested slides into chunks and find out if they exist
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

// now find the attributes to modify
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

// now start processing slides and change attributes
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

// back up and write files
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