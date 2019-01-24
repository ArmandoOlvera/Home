/*
 * Copyright (c) 2010 Brendon Boshell
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/*
 * Example Usage:
 * IframeOnClick.track(document.getElementById("iframe"), function() { alert('click'); });
 */
var IframeOnClick = {
 
	/*
	 * The period at which we scan for clicked Iframes. Should be about 200ms to be
	 * suitably responsive, but too small could cause browser to lock up.
	 *
	 */
	resolution: 200,
 
	/*
	 * Array of iframes.
	 *
	 */
	iframes: [],
 
	/*
	 * Holds a reference to setInterval for checkClick
	 *
	 */
	interval: null,
 
	/*
	 * Class to represent an individual Iframe.
	 *
	 */
	Iframe: function() {
		this.element = arguments[0];
		this.cb = arguments[1]; // our callback function
		this.hasTracked = false;
	},
 
	/*
	 * Call this function, passing your Iframe element and a callback function, and
	 * the callback function will be called when the Iframe gets clicked.
	 *
	 */
	track: function(element, cb) {
		// create new 'Iframe'
		this.iframes.push(new this.Iframe(element, cb));
 
		// start tracking, if we haven't already done so.
		if (!this.interval) {
			var _this = this;
			this.interval = setInterval(function() { _this.checkClick(); }, this.resolution);
		}
	},
 
	/*
	 * Check each of the Iframes we are tracking for clicks...
	 *
	 */
	checkClick: function() {
		if (document.activeElement) {
			var activeElement = document.activeElement;
			for (var i in this.iframes) {
				if (activeElement === this.iframes[i].element) { // user is in this Iframe
					if (this.iframes[i].hasTracked == false) { // we've not already made a call...
						this.iframes[i].cb.apply(window, []); // ...call callback function
						this.iframes[i].hasTracked = true;
					}
				} else {
					this.iframes[i].hasTracked = false;
				}
			}
		}
	}
};