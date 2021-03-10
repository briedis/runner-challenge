<?php

namespace App\Services;

use App\Models\ImageModel;
use Intervention\Image\Constraint;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use League\Flysystem\FileExistsException;
use RedBeanPHP\R;

class ImageService
{
    /**
     * Upload an image
     * @param string $pathname
     * @return int Image id
     * @deprecated Use ImageService::upload()
     */
    public function create(string $pathname): int
    {
        $manager = new ImageManager(['driver' => 'gd']);

        try {
            $image = $manager->make($pathname);
        } catch (NotReadableException $e) {
            throw new InvalidArgumentException('Failed to read the image');
        }

        $image->fit(300, 300);

        $bean = R::dispense('images');
        $bean->image = base64_encode($image->encode('jpg')->getEncoded());
        R::store($bean);

        return $bean->id;
    }

    /**
     * @param int $imageId
     * @return string|null base64 URI
     */
    public function getImageContent(int $imageId)
    {
        $bean = R::findOne('images', 'id = ?', [$imageId]);
        if (!$bean) {
            return null;
        }

        return base64_decode($bean->image);
    }

    /**
     * @param string $sourcePathname
     * @param string $targetDirectory
     * @return ImageModel
     */
    public function upload(string $sourcePathname, string $targetDirectory): ImageModel
    {
        $manager = new ImageManager(['driver' => 'gd']);

        try {
            $image = $manager->make($sourcePathname);
        } catch (NotReadableException $e) {
            throw new InvalidArgumentException('Failed to read the image');
        }

        $extension = strpos(strtolower($image->mime()), 'png') !== false ? 'png' : 'jpg';

        $model = $this->createEntry($extension, $targetDirectory);

        try {
            $this->preprocessAndSave($model, $image);
        } catch (FileExistsException $e) {
            throw new InvalidArgumentException('Image saving failed');
        }

        return $model;
    }

    /**
     * @param ImageModel $model
     * @param Image $image
     * @throws FileExistsException
     */
    private function preprocessAndSave(ImageModel $model, Image $image)
    {
        $image = $image->orientate();

        $constraint = function (Constraint $constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        };

        $image->resize(1000, 1000, $constraint);

        $model->getStorage()->writeStream(
            $model->getLargeFilename(),
            $image->stream($model->extension, 90)->detach()
        );

        $image->resize(400, 400, $constraint);

        $model->getStorage()->writeStream(
            $model->getSmallFilename(),
            $image->stream($model->extension, 90)->detach()
        );

        $image->destroy();
    }

    private function createEntry(string $extension, string $targetDirectory)
    {
        $image = new ImageModel;
        $image->directory = $targetDirectory;
        $image->extension = $extension;
        $image->createdAt = time();

        $image->save();

        return $image;
    }
}