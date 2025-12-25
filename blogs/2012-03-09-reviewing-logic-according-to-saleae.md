---
title: "reviewing logic according to saleae"
date: 2012-03-09
private: false
---

This isn't really a review, so ignore the title.  It's just musings and thoughts.

I've been playing with a new toy.  I MEAN, A TOOL, NOT A TOY.  To engineers pretty much all tools are toys.  For work, I got a LeCroy Waverunner oscilloscope/logic analyzer.  It's delicious.  Really.  Not only is it amazingly functional, but it's aesthetically about as breathtaking as lab equipment gets.  Just look.  I'm already sidetracked though, because this isn't the toy that I'm talking about.  Here's a picture of the LeCroy unit anyway.  Why yes, the screen rotates into portrait mode to help you out when using it as a 32 channel logic analyzer...

![](/media/waverunner-265x300.png)
What I meant to start talking about is the  Saleae Logic logic analyzer.  Hopefully, proper capitalization doesn't confuse you regarding the name of the device and its function.  It's about as sexy as a tiny USB analyzer can be.  It's like a tiny Mac Mini, or Apple TV, in black, with one connection on each side.  Pictured is Logic16, which is the LARGE version.  Both the Logic and the Logic16 are pretty tiny.

![](/media/logic11-300x230.jpg)

Putting aside the fact that it works, the software is sleek, elegant, and intuitive.  At first, I was put off by the lack of menus, options, and configurability...and then I realized, it was in the Apple class of *it just works**.  Disclaimer: I don't like Apple, and I don't feel for me that it just *anythings*.  The plan is to use it for some personal projects, but I brought it with me to work to check out some CANbus stuff, and see how the serial decoders work.  I just like calling it CAN, normally, but its really hard to google for the acronym Controller Area Network, because "can" is a pretty common word.  So, you're welcome, googlers, for helping you get here.

I was overwhelmed with sadness when the unit initially would not read or decode anything on CAN bus.  So much for *just working*.  But, fear not oh ye will vote for me in the 2028 presidential election...Saleae seems to love open source, so I downloaded the source for their CAN serial analyzer.  Within a few minutes, I found that my data stream was inverted from typical applications...something I've known for awhile, but always basically ignored.  My setup is nonstandard, and Saleaa's analyzer had it the typical way.

Not only was the source available to me, they also provide clear instructions for setting up and compiling with a ready-made Visual Studio project.   In under an hour, I had edited the source and recompiled for my particular application.

This was a triumph of open source.  It was great to be able to quickly and easily modify their application to suit my needs.  Customer service is quick and knowledgeable...alright, customer service is mostly the engineers who designed it.  It's a big plus in my book.

Bottom line; if you are looking for an inexpensive hobbiest analyzer, pick up a Saleae Logic for $150-ish.  Do it.  If your budget is more in the $15,000 range, I'd suggest a tricked out Lecroy Waverunner.