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
