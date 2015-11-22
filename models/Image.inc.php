<?php
namespace core\models;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * Image is an image manipulatie class
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
class Image extends Model
{

    /**
     * 
     * @var \core\services\FileData
     */
    private $fileData;

    /**
     * PHP 5 constructor
     *
     * @param \Builder $builder
     * @param \core\services\Validation $validation
     * @param \core\services\FileData $fileData
     */
    public function __construct(\Builder $builder, \core\services\Validation $validation, \core\services\FileData $fileData)
    {
        parent::__construct($builder, $validation);
        
        $this->fileData = $fileData;
    }

    public function getMimeType($s_file)
    {
        return $this->fileData->getMimeType($s_file, true);
    }

    /**
     * Resizes the given picture
     * Works only with jpg, gif and png
     *
     * @param String $s_file
     *            file url
     * @param int $i_maxWidth
     *            width
     * @param int $i_maxHeight
     *            max height
     * @throws Exception the file is not a jpg,gif or png image
     */
    public function resizeImage($s_file, $i_maxWidth, $i_maxHeight)
    {
        $a_mimetype = explode('/', $this->fileData->getMimeType($s_file));
        $obj_image = null;
        
        if ($a_mimetype[1] == 'jpg' || $a_mimetype[1] == 'jpeg') {
            $s_type = 'jpg';
            $obj_image = imagecreatefromjpeg($s_file);
        } else 
            if ($a_mimetype[1] == 'gif') {
                $s_type = 'gif';
                $obj_image = imagecreatefromgif($s_file);
            } else 
                if ($a_mimetype[1] == 'png') {
                    $s_type = 'png';
                    $obj_image = imagecreatefrompng($s_file);
                } else {
                    throw new Exception("Invalid file " . $s_file);
                }
        
        $i_ratio = 1;
        $i_width = imagesx($obj_image);
        $i_height = imagesy($obj_image);
        
        if ($i_width > $i_height) {
            if ($i_width > $i_maxWidth) {
                $i_ratio = $i_maxWidth / $i_width;
            } else 
                if ($i_height > $i_maxHeight) {
                    $i_ratio = $i_maxHeight / $i_height;
                }
        } else 
            if ($i_height > $i_width) {
                if ($i_height > $i_maxHeight) {
                    $i_ratio = $i_maxHeight / $i_height;
                } else 
                    if ($i_width > $i_maxWidth) {
                        $i_ratio = $i_maxWidth / $i_width;
                    }
            }
        
        $i_maxWidth = round($i_width * $i_ratio);
        $i_maxHeight = round($i_height * $i_ratio);
        
        $obj_trumb = null;
        
        if ($i_ratio != 1) {
            if ($a_mimetype[1] == 'jpg' || $a_mimetype[1] == 'jpeg') {
                $obj_trumb = $this->resize($obj_image, $s_type, $i_maxWidth, $i_maxHeight);
                imagejpeg($obj_trumb, $s_file, 83);
            } else 
                if ($a_mimetype[1] == 'gif') {
                    $obj_trumb = $this->resize($obj_image, $s_type, $i_maxWidth, $i_maxHeight);
                    imagegif($obj_trumb, $s_file);
                } else 
                    if ($a_mimetype[1] == 'png') {
                        $obj_trumb = $this->resize($obj_image, $s_type, $i_maxWidth, $i_maxHeight);
                        imagepng($obj_trumb, $s_file, 3);
                    }
        }
        
        if (! is_null($obj_image)) {
            imagedestroy($obj_image);
        }
        
        if (! is_null($obj_trumb)) {
            imagedestroy($obj_trumb);
        }
    }

    /**
     * Generates a trumbnail with max width 50 pixels.
     * Works only with jpg, gif and png
     *
     * @param String $s_file
     *            file url
     */
    public function makeTrumb($s_file)
    {
        $a_mimetype = explode('/', $this->fileData->getMimeType($s_file));
        
        $s_extension = substr($s_file, strrpos($s_file, '.'));
        $s_destination = str_replace($s_extension, '_trumb' . $s_extension, $s_file);
        $obj_image = null;
        
        if ($a_mimetype[1] == 'jpg' || $a_mimetype[1] == 'jpeg') {
            $obj_image = imagecreatefromjpeg($s_file);
            $obj_trumb = $this->makeTrumbProcess($obj_image, 'jpeg');
            imagejpeg($obj_trumb, $s_destination, 83);
        } else 
            if ($a_mimetype[1] == 'gif') {
                $obj_image = imagecreatefromgif($s_file);
                $obj_trumb = $this->makeTrumbProcess($obj_image, 'gif');
                imagegif($obj_trumb, $s_destination);
            } else 
                if ($a_mimetype[1] == 'png') {
                    $obj_image = imagecreatefrompng($s_file);
                    $obj_trumb = $this->makeTrumbProcess($obj_image, 'png');
                    imagepng($obj_trumb, $s_destination, 3);
                }
        
        if (! is_null($obj_image)) {
            imagedestroy($obj_image);
            imagedestroy($obj_trumb);
        }
    }

    /**
     * Makes the trumb
     *
     * @param resource $obj_image
     *            image
     * @param String $s_type
     *            type (gif|png|jpeg)
     * @return resource The trumbnail
     */
    protected function makeTrumbProcess($obj_image, $s_type)
    {
        $i_width = imagesx($obj_image);
        $i_height = imagesy($obj_image);
        
        $i_desiredWidth = 110;
        
        $fl_factor = $i_desiredWidth / $i_width;
        $i_desiredHeight = round($fl_factor * $i_height);
        
        return $this->resize($obj_image, $s_type, $i_desiredWidth, $i_desiredHeight);
    }

    /**
     * Resizes the given image
     *
     * @param resource $obj_image
     *            image
     * @param String $s_type
     *            type (gif|png|jpeg)
     * @param int $i_desiredWidth
     *            width
     * @param int $i_desiredHeight
     *            height
     * @return resource resized image
     */
    protected function resize($obj_image, $s_type, $i_desiredWidth, $i_desiredHeight)
    {
        $i_width = imagesx($obj_image);
        $i_height = imagesy($obj_image);
        
        /* create a new, "virtual" image */
        $obj_virtualImage = imagecreatetruecolor($i_desiredWidth, $i_desiredHeight);
        
        /* Check alpha */
        if (($s_type == 'gif') || ($s_type == 'png')) {
            $i_alpha = imagecolortransparent($obj_image);
            
            if ($i_alpha >= 0) {
                // Get the original image's transparent color's RGB values
                $a_alphaColors = imagecolorsforindex($obj_image, $i_alpha);
                $i_alpha = imagecolorallocate($obj_virtualImage, $a_alphaColors['red'], $a_alphaColors['green'], $a_alphaColors['blue']);
                imagefill($obj_virtualImage, 0, 0, $i_alpha);
                imagecolortransparent($obj_virtualImage, $i_alpha);
            }
        } else 
            if ($s_type == 'png') {
                imagealphablending($obj_virtualImage, false);
                $i_color = imagecolorallocatealpha($obj_virtualImage, 0, 0, 0, 127);
                imagefill($obj_virtualImage, 0, 0, $i_color);
                imagesavealpha($obj_virtualImage, true);
            }
        
        /* copy source image at a resized size */
        imagecopyresampled($obj_virtualImage, $obj_image, 0, 0, 0, 0, $i_desiredWidth, $i_desiredHeight, $i_width, $i_height);
        
        return $obj_virtualImage;
    }
}