<?php

namespace Portalbox\Model;

/**
 * Work with the filesystem containing badge images
 */
class ImageModel {
	/**
	 * Get the list of badge images on the web server
	 *
	 * @todo make the directory configurable?
	 * @todo be more defensive?
	 * @todo unit test
	 */
	public function search(): array {
		$directory = __DIR__ . '/../../public/images/badges';
		return array_values(array_diff(scandir($directory), ['..', '.']));
	}
}
