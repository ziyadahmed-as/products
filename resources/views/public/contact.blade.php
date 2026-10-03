@extends('layouts.public')
@section('title', 'Contact Us')

@section('content')
<div class="pub-container" style="padding-top:4rem;padding-bottom:6rem;">
    
    <div class="pub-contact-layout">
        
        <div class="pub-contact-info pub-fade">
            <div class="pub-hero-label" style="margin-bottom:1rem;display:inline-block;">Get in Touch</div>
            <h2>Contact <span class="pub-text-gradient">Our Team</span></h2>
            <p>We are here to answer any questions you may have about our products or services. Reach out to us and we'll respond as soon as we can.</p>

            <div class="pub-contact-item">
                <div class="pub-contact-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h4>Email Us</h4>
                    <p>{{ \App\Models\Setting::get('contact_email', 'info@albareck.local') }}</p>
                </div>
            </div>

            <div class="pub-contact-item">
                <div class="pub-contact-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                    <h4>Call Us</h4>
                    <p>{{ \App\Models\Setting::get('contact_phone', '+1 (234) 567-8900') }}</p>
                </div>
            </div>

            <div class="pub-contact-item">
                <div class="pub-contact-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h4>Headquarters</h4>
                    <p>{!! nl2br(e(\App\Models\Setting::get('contact_address', "123 Industrial Parkway\nManufacturing District, TX 75001"))) !!}</p>
                </div>
            </div>
        </div>

        <div class="pub-contact-form pub-fade pub-fade-d1">
            <h2>Send us a Message</h2>
            
            @if(session('status') === 'message-sent')
                <div class="pub-flash-success" style="margin-bottom:1.5rem;">
                    <div class="pub-flash-icon"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg></div>
                    <span>Your message has been sent. We'll be in touch!</span>
                </div>
            @endif

            <form action="{{ route('public.contact.send') }}" method="POST">
                @csrf
                <div class="pub-form-grid">
                    <div class="pub-form-group">
                        <label for="name" class="pub-form-label">Name <span class="req">*</span></label>
                        <input type="text" id="name" name="name" class="pub-input" required>
                    </div>
                    <div class="pub-form-group">
                        <label for="email" class="pub-form-label">Email <span class="req">*</span></label>
                        <input type="email" id="email" name="email" class="pub-input" required>
                    </div>
                </div>
                <div class="pub-form-group" style="margin-bottom:1rem;">
                    <label for="subject" class="pub-form-label">Subject</label>
                    <input type="text" id="subject" name="subject" class="pub-input">
                </div>
                <div class="pub-form-group" style="margin-bottom:1.5rem;">
                    <label for="message" class="pub-form-label">Message <span class="req">*</span></label>
                    <textarea id="message" name="message" class="pub-input" rows="5" style="resize:vertical;" required></textarea>
                </div>
                <button type="submit" class="pub-btn pub-btn-primary pub-btn-full">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Message
                </button>
            </form>
        </div>

    </div>

</div>
@endsection

