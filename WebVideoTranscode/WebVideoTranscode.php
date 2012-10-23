<?php
/**
 * WebVideoTranscode provides:
 *  encode keys
 *  encode settings
 *
 * 	extends api to return all the streams
 *  extends video tag output to provide all the available sources
 */

/**
 * Main WebVideoTranscode Class hold some constants and config values
 */
class WebVideoTranscode {
	/**
	* Key constants for the derivatives,
	* this key is appended to the derivative file name
	*
	* If you update the wgDerivativeSettings for one of these keys
	* and want to re-generate the video you should also update the
	* key constant. ( Or just run a maintenance script to delete all
	* the assets for a given profile )
	*
	* Msg keys for derivatives are set as follows:
	* $messages['timedmedia-derivative-200_200kbs.ogv'] => 'Ogg 200';
	*/

	// Ogg Profiles
	const ENC_OGV_160P = '160p.ogv';
	const ENC_OGV_360P = '360p.ogv';
	const ENC_OGV_480P = '480p.ogv';
	const ENC_OGV_720P = '720p.ogv';

	// WebM profiles:
	const ENC_WEBM_160P = '160p.webm';
	const ENC_WEBM_360P = '360p.webm';
	const ENC_WEBM_480P = '480p.webm';
	const ENC_WEBM_720P = '720p.webm';

	// mp4 profiles:
	const ENC_H264_320P = '320p.mp4';
	const ENC_H264_480P = '480p.mp4';
	const ENC_H264_720P = '720p.mp4';

	// Static cache of transcode state per instantiation
	public static $transcodeState = array() ;

	/**
	* Encoding parameters are set via firefogg encode api
	*
	* For clarity and compatibility with passing down
	* client side encode settings at point of upload
	*
	* http://firefogg.org/dev/index.html
	*/
	public static $derivativeSettings = array(
		WebVideoTranscode::ENC_OGV_160P =>
			array(
				'maxSize'                    => '288x160',
				'videoBitrate'               => '160',
				'framerate'                  => '15',
				'audioQuality'               => '-1',
				'samplerate'                 => '44100',
				'channels'                   => '2',
				'noUpscaling'                => 'true',
				//'twopass'                    => 'true',
				'keyframeInterval'           => '128',
				'bufDelay'                   => '256',
				'videoCodec'                 => 'theora',
			),
		WebVideoTranscode::ENC_OGV_360P =>
			array(
				'maxSize'                    => '640x360',
				'videoBitrate'               => '512',
				'audioQuality'               => '1',
				'samplerate'                 => '44100',
				'channels'                   => '2',
				'noUpscaling'                => 'true',
				//'twopass'                    => 'true',
				'keyframeInterval'           => '128',
				'bufDelay'                   => '256',
				'videoCodec'                 => 'theora',
			),
		WebVideoTranscode::ENC_OGV_480P =>
			array(
				'maxSize'                    => '854x480',
				'videoBitrate'               => '1024',
				'audioQuality'               => '2',
				'samplerate'                 => '44100',
				'channels'                   => '2',
				'noUpscaling'                => 'true',
				//'twopass'                    => 'true',
				'keyframeInterval'           => '128',
				'bufDelay'                   => '256',
				'videoCodec'                 => 'theora',
			),

		WebVideoTranscode::ENC_OGV_720P =>
			array(
				'maxSize'                    => '1280x720',
				'videoQuality'               => 6,
				'audioQuality'               => 3,
				'noUpscaling'                => 'true',
				'keyframeInterval'           => '128',
				'videoCodec'                 => 'theora',
			),

		// WebM transcode:
		WebVideoTranscode::ENC_WEBM_160P =>
			array(
				'maxSize'                    => '288x160',
				'videoBitrate'               => '256',
				'audioQuality'               => '-1',
				'samplerate'                 => '44100',
				'channels'                   => '2',
				'noUpscaling'                => 'true',
				'twopass'                    => 'true',
				'keyframeInterval'           => '128',
				'bufDelay'                   => '256',
				'videoCodec'                 => 'vp8',
			),
		WebVideoTranscode::ENC_WEBM_360P =>
			array(
				'maxSize'                    => '640x360',
				'videoBitrate'               => '512',
				'audioQuality'               => '1',
				'samplerate'                 => '44100',
				'noUpscaling'                => 'true',
				'twopass'                    => 'true',
				'keyframeInterval'           => '128',
				'bufDelay'                   => '256',
				'videoCodec'                 => 'vp8',
			),
		WebVideoTranscode::ENC_WEBM_480P =>
			array(
				'maxSize'                    => '854x480',
				'videoBitrate'               => '1024',
				'audioQuality'               => '2',
				'samplerate'                 => '44100',
				'noUpscaling'                => 'true',
				'twopass'                    => 'true',
				'keyframeInterval'           => '128',
				'bufDelay'                   => '256',
				'videoCodec'                 => 'vp8',
			),
		WebVideoTranscode::ENC_WEBM_720P =>
			 array(
				'maxSize'                    => '1280x720',
				'videoQuality'               => 7,
				'audioQuality'               => 3,
				'noUpscaling'                => 'true',
				'videoCodec'                 => 'vp8',
			),

		// Losly defined per PCF guide to mp4 profiles:
		// https://develop.participatoryculture.org/index.php/ConversionMatrix
		// and apple HLS profile guide:
		// https://developer.apple.com/library/ios/#documentation/networkinginternet/conceptual/streamingmediaguide/UsingHTTPLiveStreaming/UsingHTTPLiveStreaming.html#//apple_ref/doc/uid/TP40008332-CH102-DontLinkElementID_24

		WebVideoTranscode::ENC_H264_320P =>
			array(
				'maxSize' => '480x320',
				'videoCodec' => 'h264',
				'preset' => 'ipod320',
				'videoBitrate' => '400k',
				'audioCodec' => 'aac',
				'channels' => '2',
				'audioBitrate' => '40k',
			),

		WebVideoTranscode::ENC_H264_480P =>
			array(
				'maxSize' => '640x480',
				'videoCodec' => 'h264',
				'preset' => 'ipod640',
				'videoBitrate' => '1200k',
				'audioCodec' => 'aac',
				'channels' => '2',
				'audioBitrate' => '64k',
			),

		WebVideoTranscode::ENC_H264_720P =>
			array(
				'maxSize' => '1280x720',
				'videoCodec' => 'h264',
				'preset' => '720p',
				'videoBitrate' => '2500k',
				'audioCodec' => 'aac',
				'channels' => '2',
				'audioBitrate' => '128k',
			),
	);

	/**
	 * @param $file File
	 * @param $transcodeKey string
	 * @return string
	 */
	static public function getDerivativeFilePath( &$file, $transcodeKey){
		return dirname(
				$file->getThumbPath(
					$file->thumbName( array() )
				)
			) . '/' .
			$file->getName() . '.' .
			$transcodeKey ;
	}

	/**
	 * Get temp file at target path for video encode
	 *
	 * @param $file File
	 * @param $transcodeKey String
	 *
	 * @return TempFSFile at target encode path
	 */
	static public function getTargetEncodeFile( &$file, $transcodeKey ){
		$filePath = self::getDerivativeFilePath( $file, $transcodeKey );
		$ext = strtolower( pathinfo( "$filePath", PATHINFO_EXTENSION ) );

		// Create a temp FS file with the same extension
		$tmpFile = TempFSFile::factory( 'transcode_' . $transcodeKey, $ext);
		if ( !$tmpFile ) {
			return False;
		}
		return $tmpFile;
	}

	/**
	 * Get the max size of the web stream ( constant bitrate )
	 * @return int
	 */
	static public function getMaxSizeWebStream(){
		global $wgEnabledTranscodeSet;
		$maxSize = 0;
		foreach( $wgEnabledTranscodeSet as $transcodeKey ){
			if( isset( self::$derivativeSettings[$transcodeKey]['videoBitrate'] ) ){
				$maxSize = self::$derivativeSettings[$transcodeKey]['maxSize'];
			}
		}
		return $maxSize;
	}

	/**
	 * Give a rough estimate on file size
	 * Note this is not always accurate.. especially with variable bitrate codecs ;)
	 * @param $file File
	 * @param $transcodeKey string
	 * @return number
	 */
	static public function getProjectedFileSize( $file, $transcodeKey ){
		$settings = self::$derivativeSettings[$transcodeKey];
		if( $settings[ 'videoBitrate' ] && $settings['audioBitrate'] ){
			return $file->getLength() * 8 * (
				self::$derivativeSettings[$transcodeKey]['videoBitrate']
				+
				self::$derivativeSettings[$transcodeKey]['audioBitrate']
			);
		}
		// Else just return the size of the source video ( we have no idea how large the actual derivative size will be )
		return $file->getLength() * $file->getHandler()->getBitrate( $file ) * 8;
	}

	/**
	 * Static function to get the set of video assets
	 * Checks if the file is local or remote and grabs respective sources
	 * @param $file File
	 * @param $options array
	 * @return array|mixed
	 */
	static public function getSources( &$file , $options = array() ){
		if( $file->isLocal() || $file->repo instanceof ForeignDBViaLBRepo ){
			return self::getLocalSources( $file , $options );
		} else {
			return self::getRemoteSources( $file , $options );
		}
	}

	/**
	 * Grabs sources from the remote repo via ApiQueryVideoInfo.php entry point.
	 *
	 * Because this works with commons regardless of whether TimedMediaHandler is installed or not
	 * @param $file File
	 * @param $options array
	 * @return array|mixed
	 */
	static public function getRemoteSources(&$file , $options = array() ){
		global $wgMemc;
		// Setup source attribute options
		$dataPrefix = in_array( 'nodata', $options )? '': 'data-';

		// Use descriptionCacheExpiry as our expire for timed text tracks info
		if ( $file->repo->descriptionCacheExpiry > 0 ) {
			wfDebug("Attempting to get sources from cache...");
			$key = $file->repo->getLocalCacheKey( 'WebVideoSources', 'url', $file->getName() );
			$sources = $wgMemc->get($key);
			if ( $sources ) {
				wfDebug("Success found sources in local cache\n");
				return $sources;
			}
			wfDebug("source cache miss\n");
		}

		wfDebug("Get Video sources from remote api for " . $file->getTitle()->getDBKey() . "\n");
		$query = array(
			'action' => 'query',
			'prop' => 'videoinfo',
			'viprop' => 'derivatives',
			'titles' => $file->getTitle()->getFullText()
		);
		$data = $file->repo->fetchImageQuery( $query );

		if( isset( $data['warnings'] ) && isset( $data['warnings']['query'] )
			&& $data['warnings']['query']['*'] == "Unrecognized value for parameter 'prop': videoinfo" )
		{
			// Commons does not yet have TimedMediaHandler.
			// Use the normal file repo system single source:
			return array( self::getPrimarySourceAttributes( $file, array( $dataPrefix ) ) );
		}
		$sources = array();
		// Generate the source list from the data response:
		if( isset( $data['query'] ) && $data['query']['pages'] ){
			$vidResult = array_shift( $data['query']['pages'] );
			if( isset( $vidResult['videoinfo'] ) ) {
				$derResult = array_shift( $vidResult['videoinfo'] );
				$derivatives = $derResult['derivatives'];
				foreach( $derivatives as $derivativeSource ){
					$sources[] = $derivativeSource;
				}
			}
		}

		// Update the cache:
		if ( $sources && $file->repo->descriptionCacheExpiry > 0 ) {
			$wgMemc->set( $key, $sources, $file->repo->descriptionCacheExpiry );
		}

		return $sources;

	}

	/**
	 * Based on the $wgEnabledTranscodeSet set of enabled derivatives we
	 * sync the database with $wgEnabledTranscodeSet and return sources that are ready
	 *
	 * If no transcode is in progress or ready add the job to the jobQueue
	 *
	 * @param $file File object
	 * @param $options array Options, a set of options:
	 * 					'nodata' Strips the data- attribute, useful when your output is not html
	 * @return array an associative array of sources suitable for <source> tag output
	 */
	static public function getLocalSources( &$file , $options=array() ){
		global $wgEnabledTranscodeSet, $wgEnableTranscode;
		$sources = array();

		// Add the original file:
		$sources[] = self::getPrimarySourceAttributes( $file, $options );

		// If $wgEnableTranscode is false don't look for or add other local sources:
		if( $wgEnableTranscode === false ){
			return $sources;
		}

		// If an "oldFile" don't look for other sources:
		if( $file->isOld() ){
			return $sources;
		}

		// Just directly return audio sources ( No transcoding for audio for now )
		if( $file->getHandler()->isAudio( $file ) ){
			return $sources;
		}

		// Setup local variables
		$fileName = $file->getName();

		$addOggFlag = false;
		$addWebMFlag = false;
		$addH264Flag = false;

		$ext = pathinfo( "$fileName", PATHINFO_EXTENSION);

		// Check the source file for .webm extension
		if( strtolower( $ext ) == 'mp4' ){

		} else 	if( strtolower( $ext )== 'webm' ) {
			$addWebMFlag = true;
		} else {
			// If not webm assume ogg as the source file
			$addOggFlag = true;
		}

		// Now Check for derivatives and add to transcode table if missing:
		foreach( $wgEnabledTranscodeSet as $transcodeKey ){
			$codec =  self::$derivativeSettings[$transcodeKey]['videoCodec'];
			// Check if we should add derivative to job queue
			// Skip if target encode larger than source
			if( self::isTargetLargerThanFile( $file, self::$derivativeSettings[$transcodeKey]['maxSize']) ){
				continue;
			}
			// if we going to try add source for this derivative, update codec flags:
			if( $codec == 'theora' ){
				$addOggFlag = true;
			}
			if( $codec == 'vp8' ){
				$addWebMFlag = true;
			}
			if( $codec == 'h264' ){
				$addH264Flag = true;
			}
			// Try and add the source
			self::addSourceIfReady( $file, $sources, $transcodeKey, $options );
		}
		// Make sure we have at least one ogg, webm and h264 encode
		// Note this only reflects any enabled derviatives in $wgEnabledTranscodeSet
		if( !$addOggFlag || !$addWebMFlag || !$addH264Flag ){
			foreach( $wgEnabledTranscodeSet as $transcodeKey ){
				if( !$addOggFlag && self::$derivativeSettings[$transcodeKey]['videoCodec'] == 'theora' ){
					self::addSourceIfReady( $file, $sources, $transcodeKey, $options );
					$addOggFlag = true;
				}
				if( !$addWebMFlag && self::$derivativeSettings[$transcodeKey]['videoCodec'] == 'vp8' ){
					self::addSourceIfReady( $file, $sources, $transcodeKey, $options );
					$addWebMFlag = true;
				}
				if( !$addH264Flag && self::$derivativeSettings[$transcodeKey]['videoCodec'] == 'h264' ){
					self::addSourceIfReady( $file, $sources, $transcodeKey, $options );
					$addH264Flag = true;
				}
			}
		}
		return $sources;
	}

	/**
	 * Get the transcode state for a given filename and transcodeKey
	 *
	 * @param $fileName string
	 * @param $transcodeKey string
	 * @return bool
	 */
	public static function isTranscodeReady( $file, $transcodeKey ){

		// Check if we need to populate the transcodeState cache:
		$transcodeState =  self::getTranscodeState( $file );

		// If no state is found the cache for this file is false:
		if( !isset( $transcodeState[ $transcodeKey ] ) ) {
			return false;
		}
		// Else return boolean ready state ( if not null, then ready ):
		return !is_null( $transcodeState[ $transcodeKey ]['time_success'] );
	}

	/**
	 * Clear the transcode state cache:
	 * @param String $fileName Optional fileName to clear transcode cache for
	 */
	public static function clearTranscodeCache( $fileName = null){
		if( $fileName ){
			unset( self::$transcodeState[ $fileName ] );
		} else {
			self::$transcodeState = array();
		}
	}

	/**
	 * Populates the transcode table with the current DB state of transcodes
	 * if transcodes are not found in the database their state is set to "false"
	 *
	 * @param {Object} File object
	 */
	public static function getTranscodeState( $file ){
		$fileName = $file->getTitle()->getDbKey();
		if( ! isset( self::$transcodeState[$fileName] ) ){
			wfProfileIn( __METHOD__ );
			// initialize the transcode state array
			self::$transcodeState[ $fileName ] = array();
			$res = $file->repo->getSlaveDB()->select( 'transcode',
					'*',
					array( 'transcode_image_name' => $fileName ),
					__METHOD__,
					array( 'LIMIT' => 100 )
			);
			// Populate the per transcode state cache
			foreach ( $res as $row ) {
				// strip the out the "transcode_" from keys
				$trascodeState = array();
				foreach( $row as $k => $v ){
					$trascodeState[ str_replace( 'transcode_', '', $k ) ] = $v;
				}
				self::$transcodeState[ $fileName ][ $row->transcode_key ] = $trascodeState;
			}
			wfProfileOut( __METHOD__ );
		}
		return self::$transcodeState[ $fileName ];
	}

	/**
	 * Remove any transcode files and db states associated with a given $file
	 *
	 * also remove the transcode files:
	 * @param $file File Object
	 * @param $transcodeKey String Optional transcode key to remove only this key
	 */
	public static function removeTranscodes( &$file, $transcodeKey = false ){

		// if transcode key is non-false, non-null:
		if( $transcodeKey ){
			// only remove the requested $transcodeKey
			$removeKeys = array( $transcodeKey );
		} else {
			// Remove any existing files ( regardless of their state )
			$res = $file->repo->getSlaveDB()->select( 'transcode',
				array( 'transcode_key' ),
				array( 'transcode_image_name' => $file->getTitle()->getDBKey() )
			);
			$removeKeys = array();
			foreach( $res as $transcodeRow ){
				$removeKeys[] = $transcodeRow->transcode_key;
			}
		}

		// Remove files by key:
		foreach( $removeKeys as $tKey){
			$filePath = self::getDerivativeFilePath( $file, $tKey );
			if( $file->repo->fileExists( $filePath ) ){
				wfSuppressWarnings();
				$res = $file->repo->quickPurge( $filePath );
				wfRestoreWarnings();
				if( !$res ){
					wfDebug( "Could not delete file $filePath\n" );
				}
			}
		}

		// Build the sql query:
		$dbw = wfGetDB( DB_MASTER );
		$deleteWhere = array( 'transcode_image_name' => $file->getTitle()->getDBkey() );
		// Check if we are removing a specific transcode key
		if( $transcodeKey !== false ){
			$deleteWhere['transcode_key'] = $transcodeKey;
		}
		// Remove the db entries
		$dbw->delete( 'transcode', $deleteWhere, __METHOD__ );

		// also remove assoicated jobs ( will be re-added on page view, or reset job request )
		$deleteJobsWhere = array(
			'job_cmd' => 'webVideoTranscode',
			'job_title' => $file->getTitle()->getDBkey()
		);
		// Remove jobs db entries
		$dbw->delete( 'job', $deleteJobsWhere, __METHOD__ );

		// Purge the cache for pages that include this video:
		self::invalidatePagesWithFile( $file->getTitle() );

		// Remove from local WebVideoTranscode cache:
		self::clearTranscodeCache(  $file->getTitle()->getDBKey()  );
	}

	/**
	 * @param $titleObj Title
	 */
	public static function invalidatePagesWithFile( &$titleObj ){
		wfDebug("WebVideoTranscode:: Invalidate pages that include: " . $titleObj->getDBKey() );
		// Purge the main image page:
		$titleObj->invalidateCache();

		// TODO if the video is used in over 500 pages add to 'job queue'
		// TODO interwiki invalidation ?
		$limit = 500;
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'imagelinks', 'page' ),
			array( 'page_namespace', 'page_title' ),
			array( 'il_to' => $titleObj->getDBkey(), 'il_from = page_id' ),
			__METHOD__,
			array( 'LIMIT' => $limit + 1 )
		);
		foreach ( $res as $page ) {
			$title = Title::makeTitle( $page->page_namespace, $page->page_title );
			$title->invalidateCache();
		}
	}

	/**
	 * Add a source to the sources list if the transcode job is ready
	 * if the source is not found update the job queue
	 */
	public static function addSourceIfReady( &$file, &$sources, $transcodeKey, $dataPrefix = '' ){
		// Check if the transcode is ready:
		if( self::isTranscodeReady( $file, $transcodeKey ) ){
			$sources[] = self::getDerivativeSourceAttributes( $file, $transcodeKey, $dataPrefix );
		} else {
			self::updateJobQueue( $file, $transcodeKey );
		}
	}

	/**
	 * Get the primary "source" asset used for other derivatives
	 * @param $file File
	 * @param $options array
	 * @return array
	 */
	static public function getPrimarySourceAttributes( $file, $options = array() ){
		global $wgLang;
		$src = in_array( 'fullurl', $options)?  wfExpandUrl( $file->getUrl() ) : $file->getUrl();

		$bitrate = $file->getHandler()->getBitrate( $file );
		$metadataType = $file->getHandler()->getMetadataType( $file );

		$source = array(
			'src' => $src,
			'title' => wfMessage( 'timedmedia-source-file-desc', $metadataType )
				->numParams( $file->getWidth(), $file->getHeight() )
				->params( $wgLang->formatBitrate( $bitrate ) ),
			"shorttitle" => wfMessage(
				'timedmedia-source-file',
				wfMessage( 'timedmedia-' . $metadataType )->text()
			)->text(),
			"width" => $file->getWidth(),
			"height" => $file->getHeight(),
		);

		if( $bitrate ){
			$source["bandwidth"] = round ( $bitrate );
		}

		// For video include framerate:
		if( !$file->getHandler()->isAudio( $file ) ){
			$framerate = $file->getHandler()->getFramerate( $file );
			if( $framerate ){
				$source[ "framerate" ] = $framerate;
			}
		}
		return $source;
	}

	/**
	 * Get derivative "source" attributes
	 * @param $file File
	 * @param $transcodeKey string
	 * @param $options array
	 * @return array
	 */
	static public function getDerivativeSourceAttributes($file, $transcodeKey, $options = array() ){
		$dataPrefix = in_array( 'nodata', $options )? '': 'data-';

		$fileName = $file->getTitle()->getDbKey();

		$thumbName = $file->thumbName( array() );
		$thumbUrl = $file->getThumbUrl( $thumbName );
		$thumbUrlDir = dirname( $thumbUrl );

		list( $width, $height ) = WebVideoTranscode::getMaxSizeTransform(
			$file,
			self::$derivativeSettings[$transcodeKey]['maxSize']
		);

		$framerate = ( isset( self::$derivativeSettings[$transcodeKey]['framerate'] ) )?
						self::$derivativeSettings[$transcodeKey]['framerate'] :
						$file->getHandler()->getFramerate( $file );
		// Setup the url src:
		$src = $thumbUrlDir . '/' .$file->getName() . '.' . $transcodeKey;
		$src = in_array( 'fullurl', $options)?  wfExpandUrl( $src ) : $src;
		return array(
				'src' => $src,
				'title' => wfMessage( 'timedmedia-derivative-desc-' . $transcodeKey )->text(),
				"shorttitle" => wfMessage( 'timedmedia-derivative-' . $transcodeKey )->text(),
				"transcodekey" => $transcodeKey,

				// Add data attributes per emerging DASH / webTV adaptive streaming attributes
				// eventually we will define a manifest xml entry point.
				"width" => $width,
				"height" => $height,
				// a "ready" transcode should have a bitrate:
				"bandwidth" => self::$transcodeState[$fileName][ $transcodeKey ]['final_bitrate'],
				"framerate" => $framerate,
			);
	}

	/**
	 * Update the job queue if the file is not already in the job queue:
	 * @param $file File object
	 * @param $transcodeKey String transcode key
	 */
	public static function updateJobQueue( &$file, $transcodeKey ){
		wfProfileIn( __METHOD__ );

		$fileName = $file->getTitle()->getDbKey();

		// Check if we need to update the transcode state:
		$transcodeState = self::getTranscodeState( $file );
		// Check if the job has been added:
		if( !isset( $transcodeState[ $transcodeKey ] ) || is_null( $transcodeState[ $transcodeKey ]['time_addjob'] ) ) {
			// Add to job queue and update the db
			$job = new WebVideoTranscodeJob( $file->getTitle(), array(
				'transcodeMode' => 'derivative',
				'transcodeKey' => $transcodeKey,
			) );
			$jobId = $job->insert();
			if( $jobId ){
				$db = $file->repo->getMasterDB();
				// update the transcode state:
				if( ! isset( $transcodeState[$transcodeKey] ) ){
					// insert the transcode row with jobadd time
					$db->insert(
						'transcode',
						array(
							'transcode_image_name' => $fileName,
							'transcode_key' => $transcodeKey,
							'transcode_time_addjob' => $db->timestamp(),
							'transcode_error' => "",
							'transcode_final_bitrate' => 0
						),
						__METHOD__
					);
				} else {
					// update job start time
					$db->update(
						'transcode',
						array(
							'transcode_time_addjob' => $db->timestamp()
						),
						array(
							'transcode_image_name' => $fileName,
							'transcode_key' => $transcodeKey,
						),
						__METHOD__
					);
				}
				// Clear the state cache ( now that we have updated the page )
				self::clearTranscodeCache( $fileName );
			}
			// no jobId ? error out in some way?
		}
		wfProfileOut( __METHOD__ );
	}

	/**
	 * Transforms the size per a given "maxSize"
	 *  if maxSize is > file, file size is used
	 * @param $file File
	 * @param $targetMaxSize int
	 * @return array
	 */
	public static function getMaxSizeTransform( &$file, $targetMaxSize ){
		$maxSize = self::getMaxSize( $targetMaxSize );
		$sourceWidth = intval( $file->getWidth() );
		$sourceHeight = intval( $file->getHeight() );
		$sourceAspect = intval( $sourceWidth ) / intval( $sourceHeight );
		$targetWidth = $sourceWidth;
		$targetHeight = $sourceHeight;
		if ( $sourceAspect <= $maxSize['aspect'] ) {
			if ( $sourceHeight > $maxSize['height'] ) {
				$targetHeight = $maxSize['height'];
				$targetWidth = intval( $targetHeight * $sourceAspect );
			}
		} else {
			if ( $sourceWidth > $maxSize['width'] ) {
				$targetWidth = $maxSize['width'];
				$targetHeight = intval( $targetWidth / $sourceAspect );
				//some players do not like uneven frame sizes
			}
		}
		//some players do not like uneven frame sizes
		$targetWidth += $targetWidth%2;
		$targetHeight += $targetHeight%2;
		return array( $targetWidth, $targetHeight );
	}

	/**
	 * Test if a given transcode target is larger than the source file
	 *
	 * @param $file File object
	 * @param $targetMaxSize int
	 * @return bool
	 */
	public static function isTargetLargerThanFile( &$file, $targetMaxSize ){
		$maxSize = self::getMaxSize( $targetMaxSize );
		$sourceWidth = $file->getWidth();
		$sourceHeight = $file->getHeight();
		$sourceAspect = intval( $sourceWidth ) / intval( $sourceHeight );
		if ( $sourceAspect <= $maxSize['aspect'] ) {
			return ( $maxSize['height'] > $sourceHeight );
		} else {
			return ( $maxSize['width'] > $sourceWidth );
		}
	}

	/**
	 * Return maxSize array for given maxSize setting
	 *
	 * @param $targetMaxSize int
	 * @return array
	 */
	public static function getMaxSize( $targetMaxSize ){
		$maxSize = array();
		$targetMaxSize = explode('x', $targetMaxSize);
		if (count($targetMaxSize) == 1) {
			$maxSize['width'] = intval($targetMaxSize[0]);
			$maxSize['height'] = intval($targetMaxSize[0]);
		} else {
			$maxSize['width'] = intval($targetMaxSize[0]);
			$maxSize['height'] = intval($targetMaxSize[1]);
		}
		$maxSize['aspect'] = $maxSize['width'] / $maxSize['height'];
		return $maxSize;
	}
}
