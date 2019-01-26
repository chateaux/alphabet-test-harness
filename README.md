#Alphabet Test Harness

##Test the Alphabet RNG mechanism


To get going navigate to the /bin folder and type: 

**php console.php Generate**

This will provide the following options:

  1) Play game
  2) Scaled data
  3) Get random bytes

Press "q" to exit.

##Play game (option 1)

This will allow you to play the Alphabet game. The objective is to reveal 8 winning
letters to win the first prize. The letter A counts for 2 winning letters since it is
a special letter.

##Scaled Data

This allows you to generate random numbers from a specified pool range ie. (1,80). 
Random numbers will be stored in a file found in the /data folder and are named 
according to the following format:

**data/sc-test-date-time-numbersX(min to max)-total(4)-file-n**

The file will contain an integer per line taken from the chosen pool range.

##Get random bytes

This will generate a file 1 - 10MB filled with random bytes.

#In detail

1. Please find the test harness software at this link: https://github.com/chateaux/alphabet-test-harness

You will need to download this code to your computer which you can do by clicking the "clone and download” button in GitHub. If you are comfortable with git, from your local terminal simply run the command: git clone git@github.com:chateaux/alphabet-test-harness.git and the software will be installed in the directory “alphabet-test-harnsss”.

2. Once downloaded, cd into “alphabet-test-harnsss”.

You will now need to install the libraries using composer. Please run: "composer install”. If you do not have composer installed on your local environment, it is relatively simple to setup: https://getcomposer.org/doc/00-intro.md 

For the software to work, please make sure your environment is running php 7.x, Otherwise, it will fail since we are using a library that can only produce cryptographically secure results and a prerequisite is PHP 7.x

3. To run the software, cd into the bin directory, alphabet-test-harnsss/bin and run the command “php console.php Generate”

4. The following instructions will be listed:

4.1. Play the game (for fun)
a. Set fast play iterations: 100 (play the game 1 million times)
b.Fast play (play 100 games a time)? [y/n]: y
c.This will run the game 100 times, and output the statistics of each win level, bets and collects.

4.2 Scaled data (produce custom files containing random integers from a pool of 1..n where n is an integer between (26 and 80)
a. The min value is always set as 1
b. The max value you can choose, we work between 26 and 80.
c. How many numbers to generate (10,000 - 10,000,000): select your quantity of numbers.
d. How many iterations? (1 - 4): You can do this 1 to 4 times to produce 1 to 4 unique files of data.
e. Once complete, it will list the file names and data location ie. Integers written and stored in file: data/sc-2019Jan26-09:36:56-lines500-scale1to80-files1-file#1

4.3 Get random bytes
a. Enter total MiB to generate (1-20): 1
b. Random bytes stored to file: data/rb-19-01-26-09-38-36-bytes(1MiB)