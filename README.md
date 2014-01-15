NAME 
	impressmod.php php script to modify impress.js slides

SYNOPSIS
    php impressmod.php 	[--f <file>] [--o <file>] [--x <value>] [--y <value>] [--z <value>]
    					[--rx <value>] [--ry <value>] [--rz <value>>] [--s <value>] [--fm]
    					[--steps <string>]

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
	  	comma-separated string of integers of step number, hyphen-separate ranges, and step #ids
	  	
	--help 
	  this help file
	  
	--version
	  prints version number
	  
EXAMPLES

1) php impressmod.php --f input.html --steps 3-5,7 --x -20 --y =200
	decrease data-x by 20 and set data-y to 200 on slides 3,4,5,7 of input.html and overwrite the file
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

  
  
