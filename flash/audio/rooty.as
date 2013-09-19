package
{
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.media.Sound;
	import flash.events.Event;
	import flash.events.ProgressEvent;
	import flash.media.SoundChannel;
	import flash.net.URLRequest;
	import flash.events.MouseEvent;
	import flash.events.IOErrorEvent;
	import flash.display.LoaderInfo;
	import flash.media.SoundTransform;
	
	public class rooty extends MovieClip
	{
		var audioStream:SoundChannel = null;
		var audioSnd:Sound;
		var audioFile:String;
		var audioURL:URLRequest;
		
		// The current position in the audio clip,
		// only updated when it is paused
		var position:int;
		
		// When moving the slider, audio is temporary paused
		// if the sound is currently playing.
		// This is to keep track of whether we should keep playing
		// afterwards.
		var temppause:Boolean;
		
		var sndtransform:SoundTransform;
		
		// Dynamic buttons
		var btnPlay:PlayButton;
		var btnVolume:VolumeButton;
		var btnVolumeBorder:VolumeBorder;
		
		// Constructor
		public function rooty()
		{
			position = 0;
			temppause = false;
			sndtransform = new SoundTransform(1.0);
			
			// Create buttons
			btnPlay = new PlayButton();
			btnPlay.x = 15;
			btnPlay.y = 2;
			btnPlay.setEnable(false);
			btnPlay.setPlaying(false);
			addChild(btnPlay);
			
			btnVolumeBorder = new VolumeBorder();
			btnVolumeBorder.x = 35;
			btnVolumeBorder.y = 6;
			addChild(btnVolumeBorder);
			
			btnVolume = new VolumeButton(1.0, stage);
			btnVolume.x = 35;
			btnVolume.y = 6;
			btnVolume.enabled = false;
			addChild(btnVolume);
			
			// Don't allow seeking until stream is successfully loaded
			track.allowSeeking(false);
			
			// Fetch file to play from flashVars
			var flashVars:Object = LoaderInfo(this.root.loaderInfo).parameters;
			audioFile = String(flashVars['mp3']);
			audioURL = new URLRequest(audioFile);
			
			// Load and play sound
			audioStream = null;
			audioSnd = null;
			if (audioURL)
			{
				audioSnd = new Sound();
				audioSnd.addEventListener(IOErrorEvent.IO_ERROR, errorHandler);
				audioSnd.addEventListener(Event.COMPLETE, completeHandler);
				audioSnd.addEventListener(ProgressEvent.PROGRESS, progressHandler);
				audioSnd.load(audioURL);
			}
		}
		
		public function errorHandler(event:IOErrorEvent)
		{
			// Change buttons to disabled state
			btnPlay.setEnable(false);
			btnPlay.setPlaying(false);
		}
		
		public function completeHandler(event:Event)
		{
			// Bind button events; only if sound is loaded
			btnPlay.addEventListener(MouseEvent.MOUSE_UP, btnPlayHandler);
			addEventListener("TempPause", temporaryPauseHandler);
			addEventListener("SetPosition", setPositionHandler);
			addEventListener("SetVolume", setVolumeHandler);
			btnPlay.setEnable(true);
			btnVolume.enabled = true;
			track.allowSeeking(true);
		}
		
		public function progressHandler(event:ProgressEvent)
		{
			var percent:Number = event.bytesLoaded / event.bytesTotal;
			track.updateBufferPercent(percent);
		}
		
		public function btnPlayHandler(event:MouseEvent)
		{
			// Not currently playing;
			// Play
			if (!audioStream)
			{
				if (int(position) == int(audioSnd.length))
					position = 0;
				audioStream = audioSnd.play(position);
				audioStream.soundTransform = sndtransform;
				btnPlay.setPlaying(true);
			}
			// Currently playing;
			// Stop
			else
			{
				position = audioStream.position;
				audioStream.stop();
				audioStream = null;
				btnPlay.setPlaying(false);
			}
		}
		
		public function temporaryPauseHandler(event:Event)
		{
			if (audioStream)
			{
				audioStream.stop();
				audioStream = null;
				temppause = true;
			}
			else
				temppause = false;
		}
		
		// Store new position *percentage* in property event.localX
		//		if position < 0 : keep current position
		// Store if user is done dragging in property event.localY
		public function setPositionHandler(event:MouseEvent)
		{
			if (event.localX >= 0)
				position = event.localX * audioSnd.length;
			
			// If drag is done, and if we were playing before dragging the slider,
			// continue playing at new position
			if (event.localY > 0 && temppause)
			{
				audioStream = audioSnd.play(position);
				audioStream.soundTransform = sndtransform;
				temppause = false;
			}
		}
		
		// Store new volume in property event.localX
		public function setVolumeHandler(event:MouseEvent)
		{
			if (event.localX >= 0 && event.localX <= 1)
			{
				sndtransform.volume = event.localX;
				if (audioStream)
					audioStream.soundTransform = sndtransform;
			}
		}
		
		public function updateFrame()
		{
			if (audioSnd)
			{
				if (audioStream && !temppause)
					position = audioStream.position;
				
				var percent:Number = position / audioSnd.length;
				track.updatePositionPercent(percent);
				
				// Make string for time elapsed / time total
				var cursec:int = Math.round(position / 1000);
				var lensec:int = Math.round(audioSnd.length / 1000);
				
				var current:String = String(Math.floor(cursec / 60)) + ":";
				if (cursec % 60 < 10)
					current += "0" + String(cursec % 60);
				else
					current += String(cursec % 60);
				
				var total:String = String(Math.floor(lensec / 60)) + ":";
				if (lensec % 60 < 10)
					total += "0" + String(lensec % 60);
				else
					total += String(lensec % 60);
				
				progresstext.text = current + " / " + total;
				
				// If reached end of stream, change to play button
				if (int(position) == int(audioSnd.length))
				{
					if (audioStream)
					{
						audioStream.stop();
						audioStream = null;
					}
					btnPlay.setPlaying(false);
				}
			}
		}
	}
}
