# ChaosMachineOne for PHP
A controlled random generator data for PHP. It doesn't chart visually, the objective is to generate values to store into the database (mysql)  

## What is the objective?

Sometimes we want to generate fake values for the database that are controlled and consistent. So, this library tries to add a order to chaos. 

Let's say the next exercise.  We want to generate random values for a new system (sales)

If we generate random values, the chart would look like

> ->gen('when _index<200 then idtable.value=random(-10,10,0.2)')

![random](docs/random.jpg)


Why? it is because they are random values.  So, they are right but they don't looks real because there is not a trend.

Let's generate the same value with a sine (for example, let's say that there is a cycle of sales)

> ->gen('when _index<200 then idtable.add=sin(0,0,10,30)')

![random](docs/sin1030.jpg)

The chart has a trend but it is too predictable.  So, let's add all factors.


> ->gen('when _index<200 then idtable.value=random(-10,10,0.2) and idtable.add=sin(0,0,10,30)')

![random](docs/randomsin.jpg)

While this chart is far from real, but it is not TOO RANDOM and it has a trend. 

## Range Functions (numbers)

Functions that generates a range of values


### ramp($fromX, $toX, $fromY, $toY)

It generates a ramp values (lineal values)
> ->gen('when _index<200 then idtable.value=ramp(0,100,10,1000)')

![ramp](docs/ramp.jpg)


### log($startX,$startY,$scale=1)

It generates log values

> ->gen('when _index<200 then idtable.value=log(0,0,100)')

![log](docs/log.jpg)

### exp($startX,$startY,$scale=1)

It generates exponential values. The scale is for division of Y

> ->gen('when _index<200 then idtable.value=exp(0,0,10)')

![exp](docs/exp.jpg)


### sin($startX,$startY,$speed=1,$scale=1)

It generates a sinuzoid values. The angle is calculated with the current index x the speed (in degree)  

> ->gen('when _index<200 then idtable.value=sin(0,0,1,1)')

![sin1](docs/sin1.jpg)

> ->gen('when _index<200 then idtable.value=sin(0,0,10,1)')

![sin10](docs/sin10.jpg)

### atan($startX,$startY,$speed=1,$scale=1)

It generates arc-tangent values

### parabola($centerX,$startY,$scaleA=1,$scaleB=1,$scale=1)

It generates a parabola. It is possible to invert the parabola by changing the scaleA by negative

### bell($centerX, $startY, $sigma=1, $scaleY=1)

It generates a bell values, sigma is the "wide" of the bell.

## Fixed functions (numbers)

Functions that generates a single value

### randomprop(...$args)

It generates a random value by using different probabilities.

> randomprop(1,2,3,30,50,20) 

* there is 30% for 1  
* there is 50% for 2  
* there is 20% for 3  

> ->gen('when _index<200 then idtable.value=randomprop(1,2,3,30,50,20)')

![randomprop](docs/randomprop.jpg)

### random($from,$to,$jump=1)

It generates a random value from $from to $to.

> random(1,10) // 1,2,3,4,5,6,7,8,9,10
> random(1,10,2) // 1,3,5,7,9

> ->gen('when _index<200 then idtable.value=random(-10,10,0.2)')

![random](docs/random.jpg)
