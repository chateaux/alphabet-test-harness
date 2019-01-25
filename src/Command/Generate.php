<?php
namespace Application\Command;

use Application\Service\RNGZendMathRand;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Prompt;
use Zend\Console\Prompt\Line;
use Zend\Math\Rand;
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
            $console->writeLine('Make sure your command line is using php 7 +', \Zend\Console\ColorInterface::RED);

            $options = array(
                '1' => 'Play game',
                '2' => 'Scaled data',
                '3' => 'Get random bytes',
                'q' => 'Quit...'
            );

            $answer = Prompt\Select::prompt(
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

            if ($answer == 'q') {
                $console->clearScreen();
                $loop = false;
            }
            $console->writeLine();
        }
    }

    /**
     * Play the actual game on the command line
     * @param AdapterInterface $console
     */
    private function playGame(AdapterInterface $console)
    {
        $fp = 1;
        $playCount = 0;
        $fpIterations = Line::prompt('Set fast play iterations ', false, 8 );
        $fastplay = Prompt\Confirm::prompt("Fast play (play $fpIterations games a time)? [y/n]");
        $loop = true;
        $balance = 0;
        $ticketCost = 5;
        $collect = 0;
        $bet = 0;
        $collectLevelStats = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0,  8 => 0,];
        $prize = [0 => 0, 1 => 0, 2 => 0, 3 => 15, 4 => 50, 5 => 1000, 6 => 100000, 7 => 1000000, 8 => 0,];
        $min = 1;
        $max = 49;
        $selectNumbers = 7;

        while ($loop == true) {
            $balance = $balance - $ticketCost;

            if (!$fastplay or $fp > $fpIterations) {
                $figlet = new \Zend\Text\Figlet\Figlet();
                echo $figlet->render('Play now!');
                $console->writeLine("Playing Alphabet, your balance is $$balance, each ticket costs $$ticketCost ");
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
                $table = new \Zend\Text\Table\Table([
                    'columnWidths' => [30, 20, 20]
                ]);
                $table->appendRow(['You revealed the letters:', implode(",", $result['letters']), ""]);
                $table->appendRow(['You matched:', $result['winLevel'] . ' ' . $subject, ""]);
                $table->appendRow(['You won:', "$" . $prize[$result['winLevel']], ""]);
                $table->appendRow(['Your balance is:', "$$balance", ""]);
                $table->appendRow(['Game bets:', "$$bet", ""]);
                $table->appendRow(['Game collects:', "$$collect", ""]);
                $table->appendRow(['Operator standing:', "$" . ($bet - $collect), number_format((($bet - $collect)/$bet)*100, 2, '.', '')."%"]);
                foreach ($collectLevelStats as $key => $level) {
                    $table->appendRow(["Level: $key ($$prize[$key])", "$level", "$" . ($level * $prize[$key])]);
                }
                echo $table;
            }

            if (!$fastplay or $fp > $fpIterations) {
                $fp = 0;
                $loop = Prompt\Confirm::prompt('Play again? [y/n]');
                if ($loop) {
                    $fpIterations = Line::prompt('Set fast play iterations ', false, 8 );
                    $fastplay = Prompt\Confirm::prompt("Fast play (play $fpIterations games a time)? [y/n]");
                }
            }

            $fp ++;
            $playCount ++;
            $console->clearScreen();
        }

        return;
    }

    private function scaleData(AdapterInterface $console)
    {
        $loop = true;
        while ($loop == true) {
            $figlet = new \Zend\Text\Figlet\Figlet();
            echo $figlet->render('Scaled data!');
            $console->writeLine("The minimum value is set to 1");
            $min = 1;
            $max = Line::prompt(
                'Enter a max value: (26 - 80): ',
                false,
                6
            );
            if ($max > 80) {
                $console->writeLine("Sorry, max number is 80", \Zend\Console\ColorInterface::RED);
                return;
            }
            $generateNumbers = Line::prompt(
                'How many numbers to generate (10,000 - 10,000,000): ',
                false,
                8
            );
            if ($generateNumbers > 10000000) {
                $console->writeLine("Sorry, you can not generate more than 10 million numbers", \Zend\Console\ColorInterface::RED);
                return;
            }
            $iterations = Line::prompt(
                'How many iterations? (1 - 4) will be stored in files in the /data folder using the format filename-date-file-n: ',
                false,
                8
            );
            if ($iterations > 4) {
                $console->writeLine("We only allow up to 4 iterations.", \Zend\Console\ColorInterface::RED);
                return;
            }
            $fileSize = ($generateNumbers < 347000) ? "< 1MB" : "~".number_format((float)$generateNumbers/347000, 2, '.', '')."MB";
            $filename = $this->config['fileLocation'].$this->config['filenameScale'].\Date('YMd-h:i:s').'-'.$generateNumbers.'('.$min.'to'.$max.')-total('.$iterations.')-file';
            for($n=1;$n<=$iterations;$n++) {
                $console->writeLine($n." Selecting $generateNumbers iterations from the pool ($min / $max)");
                $console->writeLine($n." Writing integers to file ($fileSize)",\Zend\Console\ColorInterface::RED);
                for($i=0;$i<$generateNumbers;$i++) {
                    $result = $this->zendMathRand->getRandomNumber($min, $max);
                    file_put_contents($filename.'-'.$n, $result.PHP_EOL, FILE_APPEND);
                }
                $console->writeLine("Integers written and stored in file: $filename-$n", \Zend\Console\ColorInterface::BLUE);
            }
            $console->writeLine('Ok we are done!',\Zend\Console\ColorInterface::BLUE);

            $loop = Prompt\Confirm::prompt('Run again? [y/n]');
            $console->clearScreen();
        }

        return;
    }

    public function getBytes(AdapterInterface $console)
    {
        $loop = true;
        while ($loop == true) {
            $figlet = new \Zend\Text\Figlet\Figlet();
            echo $figlet->render('Get bytes');
            $meg = Line::prompt(
                'Enter total MB to generate (1-20): ',
                false,
                8
            );
            if ($meg > 20) {
                $console->writeLine("Sorry, maximum file size is 20MB", \Zend\Console\ColorInterface::RED);
                return;
            }
            $console->writeLine("Saving random bytes");

            $result = Rand::getBytes($meg * 1000000);

            $filename = $this->config['fileLocation'] . $this->config['filenameBytes'] . \Date('y-m-d-h-i-s') . '-bytes(' . $meg . 'MB)';
            $console->writeLine("Random bytes stored to file: " . $filename);

            file_put_contents($filename, $result . PHP_EOL, FILE_APPEND);
            $console->writeLine('Ok we are done!', \Zend\Console\ColorInterface::BLUE);

            $loop = Prompt\Confirm::prompt('Run again? [y/n]');
            $console->clearScreen();
        }

        return;
    }
}
