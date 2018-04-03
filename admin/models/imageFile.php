<?php
/**
 * @package         RSGallery2
 * @subpackage      com_rsgallery2
 * @copyright   (C) 2016-2018 RSGallery2 Team
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @author          finnern
 * RSGallery is Free Software
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/includes/ImgWatermarkNames.php';

/**
 * Single image model
 * Db functions
 *
 * @since 4.3.0
 */
class rsgallery2ModelImageFile extends JModelList // JModelAdmin
{
	/**
	 * @var  externalImageLib contains external image library handler
	 */
	public $ImageLib = null;

	/**
	 * Constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct()
	{
		global $rsgConfig, $Rsg2DebugActive;

		parent::__construct();

		// ToDo: try catch
		// ToDo: ? fallback when lib is not existing any more ?
		
		if ($Rsg2DebugActive)
		{
			JLog::add('==>Start __construct');
		}

		// Use rsgConfig to determine which image library to load
		$graphicsLib = $rsgConfig->get('graphicsLib');
		switch ($graphicsLib)
		{
			case 'gd2':
				// return GD
				require_once JPATH_COMPONENT_ADMINISTRATOR . '/includes/ExtImgLib_GD.php';
				$this->ImageLib = new external_GD2;
				break;
			case 'imagemagick':
				//return ImageMagick
				require_once JPATH_COMPONENT_ADMINISTRATOR . '/includes/ExtImgLib_imagemagick.php';
				$this->ImageLib = new external_imagemagick;
				break;
			case 'netpbm':
				//return Netpbm
				require_once JPATH_COMPONENT_ADMINISTRATOR . '/includes/ExtImgLib_netpbm.php';
				$this->ImageLib = new external_netpbm;
				break;
			default:
				require_once JPATH_COMPONENT_ADMINISTRATOR . '/includes/ExtImgLib_Empty.php';
				$this->ImageLib = new external_empty;
				JFactory::getApplication()->enqueueMessage(JText::_('COM_RSGALLERY2_INVALID_GRAPHICS_LIBRARY') . $rsgConfig->get('graphicsLib'), 'error');

				return false;
		}
		
        if ($Rsg2DebugActive)
        {
            JLog::add('<==Exit __construct: ');
        }

	}


	/**
	 * Creates a display image with size from config
	 *
	 * @param string $originalFileName includes path (May be a different path then the original)
	 * @param Jimage $memImage
	 *
	 * @return bool true if successful
	 *
	 * @throws Exception
	 * @since 4.3.0
	 */
	public function createDisplayImageFile($originalFileName = '', $memImage = null)
	{
		global $rsgConfig;
		global $Rsg2DebugActive;

		$IsImageCreated = false;
		$IsImageLocal = false;

		try
		{
			$baseName    = basename($originalFileName);
			$imgDstPath = JPATH_ROOT . $rsgConfig->get('imgPath_display') . '/' . $baseName . '.jpg';
			
			if ($Rsg2DebugActive)
			{
				JLog::add('==> start createDisplayImageFile: "' . $originalFileName . '" -> "' . $imgDstPath . '"');
			}

			// Create memory image if not given
			if ($memImage == null)
			{
				$IsImageLocal = True;
				$imgSrcPath = JPATH_ROOT . $rsgConfig->get('imgPath_original') . '/' . $baseName;
				$memImage = JImage ($imgSrcPath);
			}

			//---- target size -------------------------------------

			// target width
			$targetWidth = $rsgConfig->get('image_width');
			// source sizes
			$imgHeight = $memImage->getHeight();
			$imgWidth  = $memImage->getWidth();

			if ($imgWidth > $imgHeight)
			{
				// landscape
				$width = $imgWidth;
				$height = ($targetWidth / $imgWidth) * $imgHeight;
			}
			else
			{
				// portrait or square
				$width  = ($targetWidth / $imgHeight) * $imgWidth;
				$height = $targetWidth;
			}


			//--- Resize and save -----------------------------------


			$memImage->resize ($width, $height, false, self::SCALE_INSIDE);

//--- Resize and save -----------------------------------
			$memImage->toFile($imgDstPath, $type);;

			// Release memory if created
			if ($IsImageLocal)
			{
				$memImage->destroy();

			}


		}
		catch (RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing createDisplayImageFile for image name: "' . $originalFileName . '"<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = JFactory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');

			if ($Rsg2DebugActive)
			{
				JLog::add($OutTxt);
			}

		}

		if ($Rsg2DebugActive)
		{
			JLog::add('<== Exit createDisplayImageFile: ' . (($IsImageCreated) ? 'true' : 'false'));
		}

		return $IsImageCreated;
	}

	/**
	 * Creates a thumb image with size from config
	 *
	 * @param $originalFileName
	 *
	 * @return bool true if successful
	 *
	 * @since 4.3.0
	 */
	public function createThumbImageFile($originalFileName)
	{
		global $rsgConfig;
		global $Rsg2DebugActive;

		$IsImageCreated = false;

		try
		{
			$thumbWidth = $rsgConfig->get('thumb_width');

			$baseName    = basename($originalFileName);
			$imgSrcPath = JPATH_ROOT . $rsgConfig->get('imgPath_original') . '/' . $baseName;
			$imgDstPath = JPATH_ROOT . $rsgConfig->get('imgPath_thumb') . '/' . $baseName . '.jpg';

			if ($Rsg2DebugActive)
			{
				JLog::add('==>start createThumbImageFile: "' . $imgSrcPath . '" -> "' . $imgDstPath . '"');
			}

// ??			$IsImageCreated = $this->ImageLib->createSquareThumb($imgSrcPath, $imgDstPath, $thumbWidth);

			// Is thumb style square // ToDo: Thumb style -> enum  // ToDo: general: Config enums
			if ($rsgConfig->get('thumb_style') == 1)
			{
				$IsImageCreated = $this->ImageLib->createSquareThumb($imgSrcPath, $imgDstPath, $thumbWidth);
			}
			else
			{
				$IsImageCreated = $this->ImageLib->resizeImage($imgSrcPath, $imgDstPath, $thumbWidth);
			}
		}
		catch (RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing createThumbImageFile for image name: "' . $originalFileName . '"<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = JFactory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');

			if ($Rsg2DebugActive)
			{
				JLog::add($OutTxt);
			}
		}

		if ($Rsg2DebugActive)
		{
			JLog::add('<== Exit createThumbImageFile: ' . (($IsImageCreated) ? 'true' : 'false'));
		}

		return $IsImageCreated;
	}

    // ToDo: add gallery ID as parameter for sub folder or sub folder itself ...
	/**
	 * @param $uploadPathFileName
	 * @param $singleFileName
	 * @param $galleryId
	 *
	 * @return bool
	 *
	 * @since 4.3.0
	 */
    public function moveFile2OriginalDir($uploadPathFileName, $singleFileName, $galleryId)
    {
        global $rsgConfig;
        global $Rsg2DebugActive;

        $isMoved = false;

        try
        {
            if ($Rsg2DebugActive)
            {
                JLog::add('==>start moveFile2OrignalDir: ');
                JLog::add('    uploadPathFileName: "' . $uploadPathFileName . '"');
                JLog::add('    singleFileName: "' . $singleFileName . '"');
            }


		if (true) {

            $dstFileName = JPATH_ROOT . $rsgConfig->get('imgPath_original') . '/'  .  $singleFileName;

            if ($Rsg2DebugActive)
            {
                JLog::add('    dstFileName: "' . $dstFileName . '"');
            }

// return $isMoved;

            $isMoved = move_uploaded_file($uploadPathFileName, $dstFileName);
        }
        }
        catch (RuntimeException $e)
        {
            $OutTxt = '';
            $OutTxt .= 'moveFile2OrignalDir: "' . $uploadPathFileName . '" -> "' . $dstFileName . '"<br>';
            $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

            $app = JFactory::getApplication();
            $app->enqueueMessage($OutTxt, 'error');

            if ($Rsg2DebugActive)
            {
                JLog::add($OutTxt);
            }
        }

	    if ($Rsg2DebugActive)
	    {
		    JLog::add('<== Exit moveFile2OriginalDir: ' . (($isMoved) ? 'true' : 'false'));
	    }

	    return $isMoved;
    }

    // ToDo: add gallery ID as parameter for sub folder or sub folder itself ...
	/**
	 * @param $srcFileName
	 * @param $dstFileName
	 * @param $galleryId
	 *
	 * @return bool
	 *
	 * @since 4.3.0
	 */
    public function copyFile2OriginalDir($srcFileName, $dstFileName, $galleryId)
    {
        global $rsgConfig;
        global $Rsg2DebugActive;

        $isCopied = false;

        try
        {
	        $dstFileName = JPATH_ROOT . $rsgConfig->get('imgPath_original') . '/'  .  $dstFileName;

            if ($Rsg2DebugActive)
            {
                JLog::add('==> start copyFile2OrignalDir: "' . $dstFileName . '"');
            }

            $isCopied = JFile::copy($srcFileName, $dstFileName);
        }
        catch (RuntimeException $e)
        {
            $OutTxt = '';
            $OutTxt .= 'copyFile2OrignalDir: "' . $srcFileName . '" -> "' . $dstFileName . '"<br>';
            $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

            $app = JFactory::getApplication();
            $app->enqueueMessage($OutTxt, 'error');

            if ($Rsg2DebugActive)
            {
                JLog::add($OutTxt);
            }
        }

	    if ($Rsg2DebugActive)
	    {
		    JLog::add('<== Exit copyFile2OrignalDir: ' . (($isCopied) ? 'true' : 'false'));
	    }

	    return $isCopied;
    }

    // create watermark -> watermark has separate class


	/**
	 * Deletes all children of given file name of RSGallery image item
	 * (original, display, thumb and watermarked representation)
	 *
	 * @param string $imageName Base filename for images to be deleted
	 * @return bool True on success
	 *
	 * @since 4.3.0
	 */
	public function deleteImgItemImages($imageName)
	{
		global $rsgConfig, $Rsg2DebugActive;

		$IsImagesDeleted = false;

		try
		{
			$IsImagesDeleted = true;

			if ($Rsg2DebugActive)
			{
				JLog::add('==> start deleteImgItemImages: "' . $imageName .'"');
			}

			// Delete existing images
			$imgPath        = JPATH_ROOT . $rsgConfig->get('imgPath_original') . '/' . $imageName;
			$IsImageDeleted = $this->DeleteImage($imgPath);
			if (!$IsImageDeleted)
			{
				$IsImagesDeleted = false;
			}

			$imgPath        = JPATH_ROOT . $rsgConfig->get('imgPath_display') . '/' . $imageName . '.jpg';
			$IsImageDeleted = $this->DeleteImage($imgPath);
			if (!$IsImageDeleted)
			{
				$IsImagesDeleted = false;
			}

			$imgPath = JPATH_ROOT . $rsgConfig->get('imgPath_thumb') . '/' . $imageName . '.jpg';;
			$IsImageDeleted = $this->DeleteImage($imgPath);
			if (!$IsImageDeleted)
			{
				$IsImagesDeleted = false;
			}


			// destination  path file name
			$watermarkFilename = ImgWatermarkNames::createWatermarkedPathFileName($imageName, 'original');
			$IsWatermarkDeleted = $this->DeleteImage($watermarkFilename);
			if (!$IsWatermarkDeleted)
			{
				$watermarkFilename = ImgWatermarkNames::createWatermarkedPathFileName($imageName, 'display');
				$IsWatermarkDeleted = $this->DeleteImage($watermarkFilename);
				if (!$IsWatermarkDeleted)
				{

				}
			}

            // Delete filename like original0817254a99efa36171c98a96a81c7214.jpg
            $imgPath = JPATH_ROOT . $rsgConfig->get('imgPath_watermarked') . '/' . $imageName;
            $IsImageDeleted = $this->DeleteImage($imgPath);
            if (!$IsImageDeleted)
            {
                // $IsImagesDeleted = false;
            }
        }
		catch (RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing deleteRowItemImages: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = JFactory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		if ($Rsg2DebugActive)
		{
			JLog::add('<== Exit deleteImgItemImages: ' . (($IsImagesDeleted) ? 'true' : 'false'));
		}

		return $IsImagesDeleted;
	}

	/**
	 * Delete given file
	 * @param string $filename
	 *
	 * @return bool True on success
	 *
	 * @since 4.3.2
	 */
	private function DeleteImage($filename='')
	{
		global $Rsg2DebugActive;

		$IsImageDeleted = true;

		try
		{
			if (file_exists($filename))
			{
				$IsImageDeleted = unlink($filename);
			}
			else
			{
				// it is not existing so it may be true
				$IsImageDeleted = true;
			}
		}
		catch (RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing DeleteImage for image name: "' . $filename . '"<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = JFactory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');

			if ($Rsg2DebugActive)
			{
				JLog::add($OutTxt);
			}

		}

		return $IsImageDeleted;
	}

	/**
	 * Moves the file to rsg...Original and creates all RSG2 images
	 * @param $uploadPathFileName
	 * @param $singleFileName
	 * @param $galleryId
	 *
	 * @return array
	 *
	 * @since 4.3.0
	 */
	public function MoveImageAndCreateRSG2Images($uploadPathFileName, $singleFileName, $galleryId)//: array
	{
		global $rsgConfig, $Rsg2DebugActive;

		if ($Rsg2DebugActive)
		{
			JLog::add('==>Start MoveImageAndCreateRSG2Images: (Imagefile)');
			JLog::add('    $uploadPathFileName: "' . $uploadPathFileName . '"');
			JLog::add('    $singleFileName: "' . $singleFileName . '"');
		}

//		if (false) {
		$urlThumbFile = '';
		$isMoved = false; // successful images
		$msg = '';

		try {
			$singlePathFileName = JPATH_ROOT . $rsgConfig->get('imgPath_original') . '/' . $singleFileName;
			if ($Rsg2DebugActive)
			{
				JLog::add('    $singlePathFileName: "' . $singlePathFileName . '"');
				$Empty = empty ($this);
				JLog::add('    $Empty: "' . $Empty . '"');
			}

			// return array($isMoved, $urlThumbFile, $msg); // file is moved

			$isMoved = $this->moveFile2OriginalDir($uploadPathFileName, $singleFileName, $galleryId);

			if (true) {

				if ($isMoved)
				{
					list($isMoved, $urlThumbFile, $msg) = $this->CreateRSG2Images($singleFileName, $galleryId);
				}
				else
				{
					// File from other user may exist
					// lead to upload at the end ....
					$msg .= '<br>' . 'Move for file "' . $singleFileName . '" failed: Other user may have tried to upload with same name at the same moment. Please try again or with different name.';
				}
			}
		}
		catch (RuntimeException $e)
		{
			if ($Rsg2DebugActive)
			{
				JLog::add('MoveImageAndCreateRSG2Images: RuntimeException');
			}

			$OutTxt = '';
			$OutTxt .= 'Error executing MoveImageAndCreateRSG2Images: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = JFactory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		if ($Rsg2DebugActive)
		{
			JLog::add('<== Exit MoveImageAndCreateRSG2Images: '
				. (($isMoved) ? 'true' : 'false')
				. ' Msg: ' . $msg);
		}

		return array($isMoved, $urlThumbFile, $msg); // file is moved
	}

	/**
	 *
	 * @param $uploadPathFileName
	 * @param $singleFileName
	 * @param $galleryId
	 *
	 * @return array
	 *
	 * @since 4.3.0
	 */
	public function CreateRSG2Images($singleFileName, $galleryId)//: array
	{
		global $rsgConfig, $Rsg2DebugActive;

		$urlThumbFile = '';
		$msg = ''; // ToDo: Raise errors instead

		if ($Rsg2DebugActive)
		{
			JLog::add('==>Start CreateRSG2Images: ' . $singleFileName );
		}


		$isCreated = false; // successful images

		// ToDo: try ... catch

		// file exists
		$singlePathFileName = JPATH_ROOT . $rsgConfig->get('imgPath_original') . '/' . $singleFileName;
		if (JFile::exists($singlePathFileName))
		{
			// Create memory image

			$imageOriginal = JImage ($singlePathFileName);

			//--- Create display  file ----------------------------------

			$isCreated = $this->createDisplayImageFile($singlePathFileName);
			if (!$isCreated)
			{
				//
				$msg .= '<br>' . 'Create Display File for "' . $singleFileName . '" failed. Use maintenance -> Consolidate image database to check it ';
			}
			else
			{   // Display file is created

				//--- Create thumb file ----------------------------------

				$isCreated = $this->createThumbImageFile($singlePathFileName);
				if (!$isCreated)
				{
					//
					$msg .= '<br>' . 'Create Thumb File for "' . $singleFileName . '" failed. Use maintenance -> Consolidate image database to check it ';
				}
				else
				{
					// Create URL for thumb
					$urlThumbFile = JUri::root() . $rsgConfig->get('imgPath_thumb') . '/' . $singleFileName . '.jpg';

					//--- Create watermark file ----------------------------------

					$isWatermarkActive = $rsgConfig->get('watermark');
					if (!empty($isWatermarkActive))
					{
						//$modelWatermark = $this->getModel('ImgWaterMark');
						$modelWatermark = $this->getInstance('imgwatermark', 'RSGallery2Model');

						$isCreated = $modelWatermark->createMarkedFromBaseName(basename($singlePathFileName), 'original');
						if (!$isCreated)
						{
							//
							$msg .= '<br>' . 'Create Watermark File for "' . $singleFileName . '" failed. Use maintenance -> Consolidate image database to check it ';
						}
					}
					else
					{
						// successful transfer
						$isCreated = true;
					}
				}
			} // display file

		}
		else
		{
			$OutTxt = ''; // ToDo: Raise errors instead
			$OutTxt .= 'CreateRSG2Images Error. Could not find original file: "' . $singlePathFileName . '"';

			$app = JFactory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');

			if ($Rsg2DebugActive)
			{
				JLog::add($OutTxt);
			}
		}

		if ($Rsg2DebugActive)
		{
			JLog::add('<== Exit CreateRSG2Images: '
				. (($isCreated) ? 'true' : 'false')
				. ' Msg: ' . $msg);
		}

		return array($isCreated, $urlThumbFile, $msg); // file is moved
	}


}
