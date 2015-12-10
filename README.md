# VisvaPHP

A class to perform path simplification in PHP using Visvalingam-Whyatt's (also known as Visvalingam-Wyatt's) algorithm

This class is used to assign a priority to each point of a set of data, using Visvalingam-Whyatt's algorithm in order to do line simplification,
which means it allows you to set a priority one time, and then filter it on runtime using each point's priority how many time you need.
This can be useful when doing client-side apps requiring line simplification.
For further information about the algorithm, see : https://hydra.hull.ac.uk/assets/hull:8338/content

WARNING : In the original V-W's algorithm it is possible to have two points with the same priority.
Here, we prefer to have a strict order between points, all points have a different priority.

USE : You can use it either by creating an instance of Visvalingam with your set of data or using the static function filterPoints.
Either way you must provide an array containing points, defined by their abscissa and ordinate values (default name is 'x' and 'y', if your points coordinates are name an other way you must specify the name as argument).
While ordinates values are always numeric, abscissa values can be either numerical or dates (either DateTime or a formated string).
To see some examples of use, have a look at examples.php

Enjoy and fork !
