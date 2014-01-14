impressmod
==========

php script to modify impress.js slides

needs simple_html_dom.php library

by andrea moro andrea@andreamoro.net

Usage and options

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
  
 
