<?php
namespace Application\Service;

use Zend\Math\Rand;

class RNGZendMathRand
{
    private function getAlphabet()
    {
        return ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    }

    public function getRandomNumber(int $min, int $max)
    {
        return Rand::getInteger($min, $max);
    }

    /**
     * The winning level is achieved by creating two random pools, the intersect is the win level.
     */
    private function getWinningLevel($selectBall, $poolStart, $poolEnd): int
    {
        $pool1 = [];
        $select1 = $selectBall;
        $pool2 = [];
        $select2 = $selectBall;
        $i = 0;
        $n = 0;
        if ($select1 > 0) {
            while ($i < $selectBall) {
                if (!in_array($num = $this->getRandomNumber($poolStart, $poolEnd), $pool1)) {
                    $pool1[] = $num;
                    $i++;
                }
            }
        }
        if ($select2 > 0) {
            while ($n < $selectBall) {
                if (!in_array($num = $this->getRandomNumber($poolStart, $poolEnd), $pool2)) {
                    $pool2[] = $num;
                    $n++;
                }
            }
        }
        $intersect = array_intersect($pool1, $pool2);
        return count($intersect);
    }

    /**
     * @param int $selectBall - total balls to be selected
     * @param int $poolStart - the start number of the pool
     * @param int $poolEnd - the end number of the pool
     * @param array $winningWordArray - the word the player needs to match as an array, ie [a,l,p,h,a,b,e,t] = alphabet
     * @param array $specialLetterArray - special letters and their win count
     * @return array
     */
    public function getRandomLetters(int $selectBall, int $poolStart, int $poolEnd, array $winningWordArray, array $specialLetterArray): array
    {
        /**
         * STEP 1 - get a win level, this is where our RNG comes into effect
         */
        $winLevel = $this->getWinningLevel($selectBall, $poolStart, $poolEnd);
        $level = $winLevel;
        $winningLetterArray = [];
        /**
         * STEP 2 - sort the letters into workable arrays
         */
        foreach ($specialLetterArray as $letter) {
            foreach ($winningWordArray as $letter2) {
                if ($letter['letter'] != $letter2) {
                    $winningLetterArray[] = [
                        'letter' => $letter2,
                        'count' => 1
                    ];
                }
            }
        }
        /**
         * STEP 3 - Add the special letters to the pool and according to their count
         */
        foreach ($specialLetterArray as $letter) {
            if ($letter['count'] === $letter['frequency']) {
                $winningLetterArray[] = [
                    'letter' => $letter['letter'],
                    'count' => $letter['count']
                ];
            } else {
                for ($i = 1; $i <= $letter['frequency']; $i++) {
                    $winningLetterArray[] = [
                        'letter' => $letter['letter'],
                        'count' => $letter['count']
                    ];
                }
            }
        }
        /**
         * STEP 4 - Build a pool of non-winning letters
         */
        $nonWinningLetterArray = $this->getAlphabet();
        foreach ($winningLetterArray as $letter) {
            if (($key = array_search($letter['letter'], $nonWinningLetterArray)) !== false) {
                unset($nonWinningLetterArray[$key]);
            }
        }
        /**
         * STEP 5 - Set the winning numbers according to the level
         */
        $letters = [];
        $i = 0;
        $overflow = 0;
        while ($i < $selectBall) {
            if ($level > 0) {
                //Get a winning letter
                $n = $this->getRandomNumber(1, count($winningLetterArray));
                $letterArray = ($winningLetterArray[$n - 1]);
                //Make sure the level count does not go into the negative
                if (($level - $letterArray['count']) >= 0 and isset($winningLetterArray[$n - 1]) and ($winningLetterArray[$n - 1] != null)) {
                    $letters[] = $letterArray['letter'];
                    unset($winningLetterArray[$n - 1]);
                    $winningLetterArray = array_merge($winningLetterArray);
                    $level = $level - $letterArray['count'];
                    $i++;
                } else {
                    $overflow++;
                    if ($overflow > 100) {
                        die("RNG Overflow, please contact support");
                    }
                }
            } else {
                //Get a losing number
                $n = $this->getRandomNumber(1, count($nonWinningLetterArray));
                if (isset($nonWinningLetterArray[$n - 1]) and $nonWinningLetterArray[$n - 1] != null) {
                    $letters[] = $nonWinningLetterArray[$n - 1];
                    unset($nonWinningLetterArray[$n - 1]);
                    $winningLetterArray = array_merge($winningLetterArray);
                    $i++;
                }
            }
        }
        /**
         * Lastly - shuffle the numbers otherwise the first letters of every scratch would be a special letter which would look silly.
         */
        shuffle($letters);
        return ['letters' => $letters, 'data' => [], 'status' => true, 'winLevel' => $winLevel];
    }
}
