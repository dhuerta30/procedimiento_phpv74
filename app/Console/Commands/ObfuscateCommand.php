<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ObfuscateCommand extends Command
{
    protected static $defaultName = 'obfuscate-code';

    protected function configure()
    {
        $this
            ->setDescription('Ofusca un archivo PHP. Ejemplo de uso php artify obfuscate-code app/Controllers/ApiController.php app/Controllers/ApiOfuscateController.php')
            ->addArgument('inputFile', InputArgument::REQUIRED, 'El archivo PHP de entrada a ofuscar.')
            ->addArgument('outputFile', InputArgument::REQUIRED, 'El archivo PHP de salida ofuscado.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFile = $input->getArgument('inputFile');
        $outputFile = $input->getArgument('outputFile');

        if (!file_exists($inputFile)) {
            $output->writeln("<error>El archivo de entrada no existe: $inputFile</error>");
            return Command::FAILURE;
        }

        // Asegurarse de que el directorio de salida exista
        $outputDir = dirname($outputFile);
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                $output->writeln("<error>No se pudo crear el directorio de salida: $outputDir</error>");
                return Command::FAILURE;
            }
        }

        $code = file_get_contents($inputFile);
        $compressedCode = gzcompress($code);
        $base64Encoded = base64_encode($compressedCode);

        $obfuscatedCode = "<?php\n";
        $obfuscatedCode .= "// El siguiente código ha sido ofuscado\n";
        $obfuscatedCode .= "\$encoded = '$base64Encoded';\n";
        $obfuscatedCode .= "\$compressed = base64_decode(\$encoded);\n";
        $obfuscatedCode .= "\$code = gzuncompress(\$compressed);\n";
        $obfuscatedCode .= "eval('?>' . \$code);\n";
        $obfuscatedCode .= "?>";

        file_put_contents($outputFile, $obfuscatedCode);

        $output->writeln("<info>El archivo ha sido ofuscado y guardado en '$outputFile'</info>");
        return Command::SUCCESS;
    }
}
