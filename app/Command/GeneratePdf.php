<?php

namespace App\Command;

use DateTime;
use Dompdf\Dompdf;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use const \APP_BASE_DIR;

#[AsCommand("generate:pdf")]
class GeneratePdf extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = new FilesystemLoader(sprintf("%s/resources/html", APP_BASE_DIR));
        $twig = new Environment($loader);
        $twig->setCache(sprintf("%s/cache", APP_BASE_DIR));

        $html = $twig->render(
            "print.twig",
            [
                "path" => sprintf("%s/resources/assets", APP_BASE_DIR)
            ]
        );

        $output->writeln($html);

        $pdfGenerator = new Dompdf();

        $pdfGenerator->getOptions()->setIsRemoteEnabled(true);  // THIS HELPED WITH LOADING FILES FROM REMOTE!!!!!!
        $pdfGenerator->getOptions()->setChroot(sprintf("%s/resources/assets", APP_BASE_DIR)); // THIS HELPED WITH LOADING FILES FROM LOCAL FILESYSTEM!!!!!!

        $pdfGenerator->loadHtml($html);
        $pdfGenerator->setPaper("A4");
        $pdfGenerator->render();

        $fileName = (new DateTime())->format("Y-m-d H:i:s");

        $fullName = sprintf("%s/resources/pdf/%s.pdf", APP_BASE_DIR, $fileName);

        file_put_contents($fullName, $pdfGenerator->output());

        return Command::SUCCESS;
    }
}