package
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import flash.events.Event;
	
	public class PositionSlider extends MovieClip
	{
		var seekallowed:Boolean;
		var seeking:Boolean;
		var handle:SliderHandle;
		
		public function PositionSlider()
		{
			seekallowed = false;
			seeking = false;
			bufferfill.width = 0;
			this.addEventListener(MouseEvent.MOUSE_DOWN, mouseDownHandler);
			stage.addEventListener(MouseEvent.MOUSE_UP, globalMouseUpHandler);
			
			handle = new SliderHandle();
			handle.x = 8;
			handle.y = 0;
		}
		
		public function allowSeeking(allow:Boolean)
		{
			seekallowed = allow;
			if (seekallowed && !contains(handle))
				addChild(handle);
			else if (contains(handle))
				removeChild(handle);
		}
		
		// Percent loaded (0.00 - 1.00)
		public function updateBufferPercent(percent:Number)
		{
			bufferfill.width = Math.round(track.width * percent);
		}
		
		// Percent through song (0.00 - 1.00)
		public function updatePositionPercent(percent:Number)
		{
			if (!seeking)
				handle.x = 8 + Math.round((track.width - handle.width) * percent);
		}
		
		public function mouseDownHandler(event:MouseEvent)
		{
			if (seekallowed)
			{
				seeking = true;
				root.dispatchEvent(new Event("TempPause"));
			}
		}
		
		public function globalMouseUpHandler(event:MouseEvent)
		{
			seeking = false;
			var mevent:MouseEvent = new MouseEvent("SetPosition", true, false, -1, 1);
			root.dispatchEvent(mevent);
		}
		
		public function updateFrame()
		{
			if (seeking)
			{
				var relx:int = mouseX - 8;
				if (relx < 0)
					relx = 0;
				if (relx > track.width - 16)
					relx = track.width - 16;
				
				var percent:Number = relx / (track.width - 16);
				handle.x = 8 + percent * (track.width - 16);
				var mevent:MouseEvent = new MouseEvent("SetPosition", true, false, percent, 0);
				root.dispatchEvent(mevent);
			}
		}
	}
}
