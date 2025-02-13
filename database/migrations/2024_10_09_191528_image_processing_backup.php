<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'keep_original_untouched',
				'value' => '1',
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Keep Original image untouched.',
				'details' => 'In case of auto rotation, the original image will be kept untouched.',
				'is_secret' => false,
			],
		];
	}
};