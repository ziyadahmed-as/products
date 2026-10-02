@extends('layouts.public')
@section('title', 'About Us')

@section('content')
<div class="pub-about-hero pub-fade">
    <div class="pub-hero-label" style="margin-bottom:1rem;display:inline-block;">Our Story</div>
    <h1>About <span class="pub-text-gradient">Albareck</span></h1>
    <p style="font-size:1.1rem;color:var(--text-2);line-height:1.7;">
        We are a premier provider of high-quality manufactured goods and raw materials. Our mission is to deliver excellence to every business we partner with.
    </p>
</div>

<div class="pub-container" style="padding-bottom:6rem;">
    <div class="pub-grid" style="grid-template-columns:1fr 1fr;gap:4rem;align-items:center;">
        
        <div class="pub-fade pub-fade-d1">
            <h2 style="font-size:1.75rem;font-weight:800;margin-bottom:1rem;">Our Vision</h2>
            <p style="color:var(--text-2);line-height:1.7;margin-bottom:2.5rem;">
                To be the global standard in industrial supply and manufacturing quality, driving innovation and efficiency for our clients worldwide.
            </p>
            
            <h2 style="font-size:1.75rem;font-weight:800;margin-bottom:1rem;">Our Mission</h2>
            <p style="color:var(--text-2);line-height:1.7;margin-bottom:2.5rem;">
                Providing robust, reliable, and sustainable products through advanced manufacturing processes and stringent quality control.
            </p>

            <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:1rem;">Why Choose Us?</h2>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:.75rem;">
                <li style="display:flex;align-items:center;gap:.75rem;color:var(--text-2);">
                    <svg style="width:20px;height:20px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Uncompromising Quality Assurance
                </li>
                <li style="display:flex;align-items:center;gap:.75rem;color:var(--text-2);">
                    <svg style="width:20px;height:20px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    State-of-the-Art Manufacturing
                </li>
                <li style="display:flex;align-items:center;gap:.75rem;color:var(--text-2);">
                    <svg style="width:20px;height:20px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Global Supply Chain Network
                </li>
            </ul>
        </div>

        <div class="pub-stat-cards pub-fade pub-fade-d2">
            <div class="pub-stat-card">
                <div class="pub-stat-num">5+</div>
                <div class="pub-stat-lbl">Global Facilities</div>
            </div>
            <div class="pub-stat-card">
                <div class="pub-stat-num" style="color:#34d399;">10k+</div>
                <div class="pub-stat-lbl">Happy Clients</div>
            </div>
            <div class="pub-stat-card">
                <div class="pub-stat-num" style="color:#818cf8;">200+</div>
                <div class="pub-stat-lbl">Products</div>
            </div>
            <div class="pub-stat-card">
                <div class="pub-stat-num" style="color:#f472b6;">15</div>
                <div class="pub-stat-lbl">Years Experience</div>
            </div>
        </div>

    </div>
</div>
@endsection
