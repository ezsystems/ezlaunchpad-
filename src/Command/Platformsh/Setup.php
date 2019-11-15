<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license   For full copyright and license information view LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace eZ\Launchpad\Command\Platformsh;

use eZ\Launchpad\Core\DockerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Setup extends DockerCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('platformsh:setup')->setDescription('Set up the Platformsh integration.');
        $this->setAliases(['psh:setup']);
    }

    protected function postAction(): void
    {
        $this->io->writeln(
            'You can also look at <comment>~/ez platformsh:deploy</comment>.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $fs = new Filesystem();
        $this->io->title($this->getDescription());

        // add a test to see if folder exists or not
        if ($fs->exists("{$this->projectPath}/.platform")) {
            if (!$this->io->confirm('You already have a <comment>.platform</comment> folder, continue?')) {
                $this->postAction();

                return;
            }
        }

        // Dump the project
        $this->taskExecutor->dumpData();

        // put the files in places
        $fs->mirror("{$this->getPayloadDir()}/platformsh/.platform", "{$this->projectPath}/.platform");

        $fs->copy(
            "{$this->getPayloadDir()}/platformsh/.platform.app.yaml",
            "{$this->projectPath}/.platform.app.yaml"
        );

        $provisioningName = $this->projectConfiguration->get('provisioning.folder_name');
        $provisioningFolder = "{$this->projectPath}/{$provisioningName}";
        $fs->copy(
            "{$this->getPayloadDir()}/platformsh/getmysqlcredentials.php",
            "{$provisioningFolder}/platformsh/getmysqlcredentials.php",
            true
        );

        $this->io->writeln(
            "Your project is now set up with Platform.sh.\n".
            "You can run <comment>git status</comment> to see the changes\n".
            "Then you just have to \n".
            "\t<comment>git add .</comment>\n".
            "\t<comment>git commit -m \"Integration Platform.sh\"</comment>\n".
            "\t<comment>git push platform {branchname}</comment>\n"
        );

        $this->postAction();
    }
}
