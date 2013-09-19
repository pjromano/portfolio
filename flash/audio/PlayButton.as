package
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	
	public class PlayButton extends MovieClip
	{
		public var mEnabled:Boolean;
		
		// If stopped (false), the icon will be the play icon
		// If playing (true), the icon will be the pause icon
		public var mPlaying:Boolean;
		
		public function PlayButton()
		{
			stop();
			buttonMode = true;
			mEnabled = true;
			mPlaying = false;
			addEventListener(MouseEvent.ROLL_OVER, mouseOverHandler);
			addEventListener(MouseEvent.ROLL_OUT, mouseOutHandler);
			addEventListener(MouseEvent.MOUSE_DOWN, mouseDownHandler);
			addEventListener(MouseEvent.MOUSE_UP, mouseUpHandler);
		}
		
		public function setEnable(enable:Boolean)
		{
			mEnabled = enable;
			if (mEnabled)
			{
				if (!mPlaying)
					gotoAndStop(1);
				else
					gotoAndStop(5);
			}
			else
				gotoAndStop(4);
		}
		
		public function setPlaying(p:Boolean)
		{
			mPlaying = p;
			if (mPlaying && currentFrame <= 3)
			{
				switch (currentFrame)
				{
					case 1:
						gotoAndStop(5);
						break;
					case 2:
						gotoAndStop(6);
						break;
					case 3:
						gotoAndStop(7);
						break;
					default:
						gotoAndStop(5);
						break;
				}
			}
			else if (!mPlaying && currentFrame >= 5)
			{
				switch (currentFrame)
				{
					case 5:
						gotoAndStop(1);
						break;
					case 6:
						gotoAndStop(2);
						break;
					case 7:
						gotoAndStop(3);
						break;
					default:
						gotoAndStop(1);
						break;
				}
			}
		}
		
		public function mouseOverHandler(event:MouseEvent)
		{
			if (mEnabled && !event.buttonDown)
			{
				if (!mPlaying)
					gotoAndStop(2);
				else
					gotoAndStop(6);
			}
		}
		
		public function mouseOutHandler(event:MouseEvent)
		{
			if (mEnabled)
			{
				if (!mPlaying)
					gotoAndStop(1);
				else
					gotoAndStop(5);
			}
		}
		
		public function mouseDownHandler(event:MouseEvent)
		{
			if (mEnabled)
			{
				if (!mPlaying)
					gotoAndStop(3);
				else
					gotoAndStop(7);
			}
		}
		
		public function mouseUpHandler(event:MouseEvent)
		{
			if (mEnabled)
			{
				if (!mPlaying)
					gotoAndStop(2);
				else
					gotoAndStop(6);
			}
		}
	}
}
