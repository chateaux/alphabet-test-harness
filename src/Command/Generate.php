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

    public function __invoke(Route $route, AdapterInterface $console)
    {
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
        }

        $console->writeLine();
    }

    private function playGame(AdapterInterface $console)
    {
        $prize = [
            0 => '$0',
            1 => '$10',
            2 => '$20',
            3 => '$30',
            4 => '$40',
            5 => '$50',
            6 => '$60',
            7 => '$70',
            8 => '$80',
        ];
        $figlet = new \Zend\Text\Figlet\Figlet();
        echo $figlet->render('Play now!');
        $console->writeLine("Playing Alphabet. ");
        $console->writeLine("To win, you must reveal the word 'ALPHABET' by scratching the card.");
        $console->writeLine("The lucky letter A counts for 2 matches!");
        $min = 1;
        $max = 26;
        $selectNumbers = 7;

        $console->writeLine("Selecting $selectNumbers numbers from a pool of $min to $max...");

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

        $console->writeLine('Scratching in progress: ');
        for ($i=0;$i<7;$i++) {
            $console->write('#');
            usleep(300000);
        }
        $console->writeLine('');
        $subject =  ($result['winLevel'] > 1) ? 'letters' : 'letter';
        $table = new \Zend\Text\Table\Table([
            'columnWidths' => [30, 20]
        ]);
        $table->appendRow(['You revealed the letters:', implode(",", $result['letters'])]);
        $table->appendRow(['You matched:', $result['winLevel'].' '.$subject]);
        $table->appendRow(['You won:', $prize[$result['winLevel']]]);
        echo $table;
        return;
    }

    private function scaleData(AdapterInterface $console)
    {
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
        $console->writeLine("Selecting $generateNumbers iterations from the pool ($min / $max)");
        $console->writeLine('Writing to file, this may take some time!',\Zend\Console\ColorInterface::RED);
        $filename = $this->config['fileLocation'].$this->config['filenameScale'].\Date('y-m-d-h-i-s').'-'.$generateNumbers.'X('.$min.'to'.$max.')-total('.$iterations.')-file';
        for($n=1;$n<=$iterations;$n++) {
            for($i=0;$i<$generateNumbers;$i++) {
                $result = $this->zendMathRand->getRandomNumber($min, $max);
                file_put_contents($filename.'-'.$n, $result.PHP_EOL, FILE_APPEND);
            }
            $console->writeLine("Integers written to file: ".$filename."-".$n);
        }
        $console->writeLine('Ok we are done!',\Zend\Console\ColorInterface::BLUE);
        return;
    }

    public function getBytes(AdapterInterface $console)
    {
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

        $result = Rand::getBytes($meg*1000000);

        $filename = $this->config['fileLocation'].$this->config['filenameBytes'].\Date('y-m-d-h-i-s').'-bytes('.$meg.'MB)';
        $console->writeLine("Random bytes stored to file: ".$filename);

        file_put_contents($filename, $result.PHP_EOL, FILE_APPEND);
        $console->writeLine('Ok we are done!',\Zend\Console\ColorInterface::BLUE);
        return;
    }
}
