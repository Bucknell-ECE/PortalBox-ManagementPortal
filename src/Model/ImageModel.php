<?php

namespace Portalbox\Model;

use Portalbox\Exception\InvalidConfigurationException;

/**
 * Work with the filesystem to locate badge images
 */
class ImageModel {
	public const EXCLUDED_NODES = [
		'.',
		'..',
		'.DS_Store',
		'thumbs.db'
	];

	public const DATA_STORE_ERROR = 'The public/images/badges directory must exist and contain the images to be used for badges';

	/**
	 * Get the list of badge images on the web server
	 *
	 * @todo make the directory configurable? This brings security risks
	 * @todo unit test? Would depend on making the directory configurable
	 */
	public function search(): array {
		$directory = __DIR__ . '/../../public/images/badges';

		$filenames = scandir($directory);
		if (!is_array($filenames)) {
			throw new InvalidConfigurationException(self::DATA_STORE_ERROR);
		}

		return array_values(array_diff($filenames, self::EXCLUDED_NODES));
	}
}
