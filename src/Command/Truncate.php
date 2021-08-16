<?php

namespace App\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class Truncate extends Command
{

	protected static $defaultName = 'app:truncate';

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function __construct(string $name = null, EntityManagerInterface $entityManager)
	{
		parent::__construct($name);

		$this->entityManager = $entityManager;
	}

	protected function configure()
	{
		$this->addArgument('table_name', InputArgument::REQUIRED, 'Table name.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$table_name = $input->getArgument('table_name');

		try {
			$this->entityManager->getConnection()->executeStatement('truncate '.$table_name);

			$output->writeln([
				'Success!',
				''
			]);

			return Command::SUCCESS;
		} catch (\Throwable $e) {
			$output->writeln([
				'Error!',
				$e->getMessage(),
				''
			]);

			return Command::FAILURE;
		}

	}
}