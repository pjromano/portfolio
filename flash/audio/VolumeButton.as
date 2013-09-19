package
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import flash.display.Shape;
	import flash.display.Stage;
	import flash.filters.GlowFilter;
	
	public class VolumeButton extends MovieClip
	{
		var mChanging:Boolean;
		var mValue:Number;
		var mFill:Shape;
		var mShadow:GlowFilter;
		var mFilters:Array;
		
		public function VolumeButton(initialVolume:Number, s:Stage)
		{
			mChanging = false;
			mValue = initialVolume;
			mFill = new Shape();
			mShadow = new GlowFilter(0xCEE2F5, 0.5, 4, 4, 6, 3);
			mFilters = new Array(mShadow);
			mFill.filters = mFilters;
			
			addEventListener(MouseEvent.MOUSE_DOWN, mouseDownHandler);
			s.addEventListener(MouseEvent.MOUSE_UP, globalMouseUpHandler);
			
			updateFrame();
			addChild(mFill);
		}
		
		public function mouseDownHandler(event:MouseEvent)
		{
			mChanging = true;
		}
		
		public function globalMouseUpHandler(event:MouseEvent)
		{
			mChanging = false;
		}
		
		public function updateFrame()
		{
			if (mChanging)
			{
				mValue = mouseX / width;
				if (mValue < 0)
					mValue = 0;
				else if (mValue > 1)
					mValue = 1;
				
				var mevent:MouseEvent = new MouseEvent("SetVolume", true, false, mValue);
				root.dispatchEvent(mevent);
			}
			
			// Draw fill for amount of volume
			mFill.graphics.clear();
			if (enabled)
			{
				mFill.graphics.beginFill(0xCEE2F5, 0.5);
				mFill.graphics.moveTo(0, height);
				mFill.graphics.lineTo(mValue * width, height);
				mFill.graphics.lineTo(mValue * width, height - mValue * height);
				mFill.graphics.lineTo(0, height);
			}
		}
	}
}
