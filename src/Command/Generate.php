<?php
namespace Application\Command;

use Application\Service\RNGZendMathRand;
use Laminas\Console\Adapter\AdapterInterface;
use Laminas\Console\ColorInterface;
use Laminas\Console\Prompt\Confirm;
use Laminas\Console\Prompt\Line;
use Laminas\Console\Prompt\Select;
use Laminas\Math\Rand;
use Laminas\Text\Figlet\Figlet;
use Laminas\Text\Table\Table;
use ZF\Console\Route;

class Generate
{
    protected $config;
    protected $zendMathRand;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->zendMathRand = new RNGZendMathRand();
    }

    /**
     * This organises the various tasks
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $loop = true;

        while ($loop == true) {
            $console->clearScreen();
            $console->writeLine('Make sure your command line is using php 7 +', ColorInterface::RED);

            $options = array(
                '1' => 'Play game',
                '2' => 'Scaled data',
                '3' => 'Get random bytes',
                '4' => 'Test RNG odds (traditional)',
                '5' => 'Test RNG odds (bonus)',
                'q' => 'Quit...'
            );

            $answer = Select::prompt(
                'Select an option: ',
                $options,
                false,
                false
            );

            if ($answer == '1') {
                $console->clearScreen();
                $this->playGame($console);
            }

            if ($answer == '2') {
                $console->clearScreen();
                $this->scaleData($console);
            }

            if ($answer == '3') {
                $console->clearScreen();
                $this->getBytes($console);
            }

            if ($answer == '4') {
                $console->clearScreen();
                $this->rngTest($console);
            }

            if ($answer == '5') {
                $console->clearScreen();
                $this->rngTestAndBonusFeature($console);
            }

            if ($answer == 'q') {
                $console->clearScreen();
                $loop = false;
            }
            $console->writeLine();
        }
    }
    private function rngTestAndBonusFeature(AdapterInterface $console)
    {
        $bigLoop = true;
        $fp = 1;
        $playCount = 0;

        while ($bigLoop) {
            $fastplay = true;
            $fpIterations = Line::prompt('Set fast play iterations (1 - 1000000): ', false, 8 );
            $loop = true;
            $balance = 0;
            $bonusBalance = 0;
            $bonusWinCount = 0;
            $ticketCost = Line::prompt('Set the ticket cost (1 - 10): ', false, 2 );
            $collect = 0;
            $bet = 0;
            $collectLevelStats = [0 => 0,1 => 0,2 => 0,3 => 0,4 => 0,5 => 0,6 => 0,7 => 0,8 => 0,9 => 0,];
            $prize[0] = 0;
            $prize[1] = Line::prompt('Set level 1: ', true );
            $prize[2] = Line::prompt('Set level 2: ', true );
            $prize[3] = Line::prompt('Set level 3: ', true );
            $prize[4] = Line::prompt('Set level 4: ', true );
            $prize[5] = Line::prompt('Set level 5: ', true );
            $prize[6] = Line::prompt('Set level 6: ', true );
            $prize[7] = Line::prompt('Set level 7: ', true );
            $prize[8] = Line::prompt('Set level 8: ', true );
            $prize[9] = Line::prompt('Set level 9: ', true );
            $bonusPrize = Line::prompt('Set bonus prize: ', false );

            $min = 1;
            $max = Line::prompt('Set the main max number 1 : (26 - 80): ', false, 2 );
            $selectNumbers = Line::prompt('How many numbers to select (1 - 9): ', false, 1 );

            $bonusMin = 1;
            $bonusMax = Line::prompt('Set the bonus number (26 - 80): ', false, 2 );
            $bonusSelectNumbers = 1;

            while ($loop == true) {
                $balance = $balance - $ticketCost;
                $console->writeLine("Count: ".$playCount);

                $winLevel = $this->zendMathRand->getWinLevel(
                    $selectNumbers,
                    $min, $max
                );

                $bonusResult = $this->zendMathRand->getWinLevel(
                    $bonusSelectNumbers,
                    $bonusMin, $bonusMax
                );

                $bet = $bet + $ticketCost;
                $collect = $collect + $prize[$winLevel];
                $collectLevelStats[$winLevel] = $collectLevelStats[$winLevel] + 1;
                $balance = $balance + $prize[$winLevel];

                if ($bonusResult == 1) {
                    $bonusBalance = $bonusBalance + $bonusPrize;
                    $balance = $balance + $bonusPrize;
                    $bonusWinCount ++;
                }

                if (!$fastplay or $fp > $fpIterations) {
                    $console->writeLine('');
                    $table = new Table([
                        'columnWidths' => [30, 20, 20]
                    ]);
                    $table->appendRow(['Customer balance is:', "$$balance", ""]);
                    $table->appendRow(['Game bets:', "$$bet", ""]);
                    $table->appendRow(['Game collects:', "$$collect", ""]);
                    $table->appendRow(['Operator profit:', "$" . ($bet - $collect), number_format((($bet - $collect)/$bet)*100, 2, '.', '')."%"]);
                    foreach ($collectLevelStats as $key => $level) {
                        $table->appendRow(["Level: $key ($$prize[$key])", "$level", "$" . ($level * $prize[$key])]);
                    }
                    $table->appendRow(["Bonus prize: ($$bonusPrize)", "".$bonusWinCount, "$" . $bonusBalance]);
                    echo $table;
                }

                if (!$fastplay or $fp > $fpIterations) {
                    $fp = 1;
                    $playCount = 0;
                    $fastplay = false;
                    $loop = false;
                    $bigLoop = Confirm::prompt('Play again? [y/n]');
                }

                $fp ++;
                $playCount ++;
                $console->clearScreen();
            }
        }

        return;
    }

    private function rngTest(AdapterInterface $console)
    {
        $bigLoop = true;
        $fp = 1;
        $playCount = 0;

        while ($bigLoop) {
            $fastplay = true;
            $fpIterations = Line::prompt('Set fast play iterations (1 - 1000000): ', false, 8 );
            $loop = true;
            $balance = 0;
            $ticketCost = Line::prompt('Set the ticket cost (1 - 10): ', false, 4 );
            $collect = 0;
            $bet = 0;
            $collectLevelStats = [0 => 0,1 => 0,2 => 0,3 => 0,4 => 0,5 => 0,6 => 0,7 => 0,8 => 0,9 => 0,];
            $prize[0] = 0;
            $prize[1] = Line::prompt('Set level 1: ', true);
            $prize[2] = Line::prompt('Set level 2: ', true );
            $prize[3] = Line::prompt('Set level 3: ', true );
            $prize[4] = Line::prompt('Set level 4: ', true );
            $prize[5] = Line::prompt('Set level 5: ', true );
            $prize[6] = Line::prompt('Set level 6: ', true );
            $prize[7] = Line::prompt('Set level 7: ', true );
            $prize[8] = Line::prompt('Set level 8: ', true );
            $prize[9] = Line::prompt('Set level 9: ', true );

            $min = 1;
            $max = Line::prompt('Set the pool max number (10 - 80): ', false, 2 );
            $selectNumbers = Line::prompt('How many numbers to select from the pool (1 - 9): ', false, 1 );

            while ($loop == true) {
                $balance = $balance - $ticketCost;

                $console->writeLine("Count: ".$playCount);

                $result = $this->zendMathRand->getWinLevel(
                    $selectNumbers,
                    $min, $max
                );

                $bet = $bet + $ticketCost;
                $collect = $collect + $prize[$result];
                $collectLevelStats[$result] = $collectLevelStats[$result] + 1;
                $balance = $balance + $prize[$result];

                if (!$fastplay or $fp > $fpIterations) {
                    $console->writeLine('');
                    $table = new Table([
                        'columnWidths' => [30, 20, 20]
                    ]);
                    $table->appendRow(['Customer balance is:', "$$balance", ""]);
                    $table->appendRow(['Game bets:', "$$bet", ""]);
                    $table->appendRow(['Game collects:', "$$collect", ""]);
                    $table->appendRow(['Operator profit:', "$" . ($bet - $collect), number_format((($bet - $collect)/$bet)*100, 2, '.', '')."%"]);
                    foreach ($collectLevelStats as $key => $level) {
                        $table->appendRow(["Level: $key ($$prize[$key])", "$level", "$" . ($level * $prize[$key])]);
                    }
                    echo $table;
                }

                if (!$fastplay or $fp > $fpIterations) {
                    $fp = 1;
                    $playCount = 0;
                    $fastplay = false;
                    $loop = false;
                    $bigLoop = Confirm::prompt('Play again? [y/n]');
                }

                $fp ++;
                $playCount ++;
                $console->clearScreen();
            }
        }

        return;
    }

    /**
     * Play the actual game on the command line
     * @param AdapterInterface $console
     */
    private function playGame(AdapterInterface $console)
    {
        $bigLoop = true;
        $fp = 1;
        $playCount = 0;
        $fastplay = false;

        while ($bigLoop) {

            while (!$fastplay) {
                $console->clearScreen();
                $fpIterations = Line::prompt('Set fast play iterations (1 - 1000000): ', false, 8 );
                $fastplay = Confirm::prompt("Fastplay (play $fpIterations games a time)? [y/n]");
            }

            $loop = true;
            $balance = 0;
            $ticketCost = Line::prompt('Set the ticket cost (1 - 10): ', false, 2 );
            $collect = 0;
            $bet = 0;
            $collectLevelStats = [
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 0,
            ];
            $prize = [
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 7,
                4 => 50,
                5 => 150,
                6 => 1000,
                7 => 5000,
                8 => 10000,
            ];
            $min = 1;
            $max = Line::prompt('Set the max number (26 - 80): ', false, 2 );
            $selectNumbers = 7;

            while ($loop == true) {
                $balance = $balance - $ticketCost;

                if (!$fastplay or $fp > $fpIterations) {
                    $figlet = new Figlet();
                    echo $figlet->render('Play now!');
                    $console->writeLine("Playing Alphabet, the customer balance is $$balance, each ticket costs $$ticketCost ");
                    $console->writeLine("To win, you must reveal the word 'ALPHABET' by scratching the card.");
                    $console->writeLine("The lucky letter A counts for 2 matches!");
                    $console->writeLine("Selecting $selectNumbers numbers from a pool of $min to $max...");
                }
                $console->writeLine("Count: ".$playCount);

                $result = $this->zendMathRand->getRandomLetters(
                    $selectNumbers,
                    $min, $max,
                    ['A', 'L', 'P', 'H', 'A', 'B', 'E', 'T'],
                    [
                        [
                            'letter' => 'A',
                            'count' => 2,
                            'frequency' => 2
                        ]
                    ]
                );

                if (!$fastplay) {
                    $console->writeLine('Scratching in progress: ');
                    for ($i = 0; $i < 7; $i++) {
                        $console->write('#');
                        usleep(200000);
                    }
                }
                $bet = $bet + $ticketCost;
                $collect = $collect + $prize[$result['winLevel']];
                $collectLevelStats[$result['winLevel']] = $collectLevelStats[$result['winLevel']] + 1;
                $balance = $balance + $prize[$result['winLevel']];

                if (!$fastplay or $fp > $fpIterations) {
                    $console->writeLine('');
                    $subject = ($result['winLevel'] > 1) ? 'letters' : 'letter';
                    $table = new Table([
                        'columnWidths' => [30, 20, 20]
                    ]);
                    $table->appendRow(['You revealed the letters:', implode(",", $result['letters']), ""]);
                    $table->appendRow(['You matched:', $result['winLevel'] . ' ' . $subject, ""]);
                    $table->appendRow(['You won:', "$" . $prize[$result['winLevel']], ""]);
                    $table->appendRow(['Customer balance is:', "$$balance", ""]);
                    $table->appendRow(['Game bets:', "$$bet", ""]);
                    $table->appendRow(['Game collects:', "$$collect", ""]);
                    $table->appendRow(['Operator profit:', "$" . ($bet - $collect), number_format((($bet - $collect)/$bet)*100, 2, '.', '')."%"]);
                    foreach ($collectLevelStats as $key => $level) {
                        $table->appendRow(["Level: $key ($$prize[$key])", "$level", "$" . ($level * $prize[$key])]);
                    }
                    echo $table;
                }

                if (!$fastplay or $fp > $fpIterations) {
                    $fp = 1;
                    $playCount = 0;
                    $fastplay = false;
                    $loop = false;
                    $bigLoop = Confirm::prompt('Play again? [y/n]');
                }

                $fp ++;
                $playCount ++;
                $console->clearScreen();
            }
        }

        return;
    }

    private function scaleData(AdapterInterface $console)
    {
        $loop = true;
        while ($loop == true) {
            $figlet = new Figlet();
            echo $figlet->render('Scaled data!');
            $console->writeLine("The minimum value is set to 1");
            $min = 1;
            $max = Line::prompt(
                'Enter a max value: (26 - 80): ',
                false,
                6
            );
            if ($max > 80) {
                $console->writeLine("Sorry, max number is 80", ColorInterface::RED);
                return;
            }
            $generateNumbers = Line::prompt(
                'How many numbers to generate (10,000 - 10,000,000): ',
                false,
                8
            );
            if ($generateNumbers > 10000000) {
                $console->writeLine("Sorry, you can not generate more than 10 million numbers", ColorInterface::RED);
                return;
            }
            $iterations = Line::prompt(
                'How many iterations? (1 - 4) will be stored in files in the /data folder using the format filename-date-file-n: ',
                false,
                8
            );
            if ($iterations > 4) {
                $console->writeLine("We only allow up to 4 iterations.", ColorInterface::RED);
                return;
            }
            $fileSize = ($generateNumbers < 347000) ? "< 1MiB" : "~".number_format((float)$generateNumbers/347000, 2, '.', '')."MiB";
            $filename = $this->config['fileLocation'].$this->config['filenameScale'].\Date('y-m-d-h-i-s') . '-' . $iterations;
            for($n=1;$n<=$iterations;$n++) {
                $console->writeLine($n." Selecting $generateNumbers iterations from the pool ($min / $max)");
                $console->writeLine($n." Writing integers to file ($fileSize)",ColorInterface::RED);
                for($i=0;$i<$generateNumbers;$i++) {
                    $result = $this->zendMathRand->getRandomNumber($min, $max);
                    file_put_contents($filename.$n.'.txt', $result.PHP_EOL, FILE_APPEND);
                }
                $console->writeLine("Integers written and stored in file: $filename$n.txt", ColorInterface::BLUE);
            }
            $console->writeLine('Ok we are done!',ColorInterface::BLUE);

            $loop = Confirm::prompt('Run again? [y/n]');
            $console->clearScreen();
        }

        return;
    }

    public function getBytes(AdapterInterface $console)
    {
        $loop = true;
        while ($loop == true) {
            $figlet = new Figlet();
            echo $figlet->render('Get bytes');
            $mib = Line::prompt(
                'Enter total MiB to generate (1-20): ',
                false,
                8
            );
            if ($mib > 20) {
                $console->writeLine("Sorry, maximum file size is 20MiB", ColorInterface::RED);
                return;
            }
            $console->writeLine("Saving random bytes");

            $result = Rand::getBytes($mib * 1048576);

            $filename = $this->config['fileLocation'] . $this->config['filenameBytes'] . \Date('y-m-d-h-i-s') . '-bytes(' . $mib . 'MiB).txt';
            $console->writeLine("Random bytes stored to file: " . $filename.".txt");

            file_put_contents($filename, $result . PHP_EOL, FILE_APPEND);
            $console->writeLine('Ok we are done!', ColorInterface::BLUE);

            $loop = Confirm::prompt('Run again? [y/n]');
            $console->clearScreen();
        }

        return;
    }
}
