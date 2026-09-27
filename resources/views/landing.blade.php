@extends('layouts.public')

@section('page-class', 'landing-page')

@section('navigation')
<div class="nav-account">
    <a href="{{ route('login') }}">Log in</a>
    <a class="button button-primary button-small" href="{{ route('register') }}">Get started</a>
</div>
@endsection

@section('content')
<div class="public-container">
    <section class="hero landing-hero" aria-labelledby="hero-title">
        <div class="hero-copy">
            <div class="eyebrow"><i class="bi bi-heart-pulse" aria-hidden="true"></i> With you, every step of the way</div>
            <h1 id="hero-title">Your health journey,<br><span>supported close to home.</span></h1>
            <p>ReproCare connects you with your local health team for pregnancy care, checkups, reminders, and trusted health information—all in one place.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="{{ route('register') }}">Start your care journey</a>
                <a class="button button-secondary" href="#features">Explore ReproCare</a>
            </div>
            <ul class="hero-trust-list" aria-label="ReproCare benefits">
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Connected to your barangay health team</li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Free and simple to use</li>
            </ul>
        </div>
        <div class="hero-visual">
            <div class="hero-visual-glow" aria-hidden="true"></div>
            <img class="hero-photo" src="{{ asset('images/community-maternal-health-education.jpg') }}" alt="Pregnant women attending a community maternal health education session" width="630" height="420" fetchpriority="high">
            <div class="photo-label"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Care, closer to home</div>
            <div class="care-note">
                <span class="care-note-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                <div><strong>Your journey. Our shared care.</strong><small>Supported by your community health team</small></div>
            </div>
        </div>
    </section>
    <div class="community-strip" aria-label="What ReproCare does for you">
        <span class="community-strip-title">Care that stays with you</span>
        <strong><i class="bi bi-calendar2-check" aria-hidden="true"></i><span>Stay ready<small>Checkups and reminders</small></span></strong>
        <strong><i class="bi bi-heart-pulse" aria-hidden="true"></i><span>Know your health<small>Records in one place</small></span></strong>
        <strong><i class="bi bi-chat-heart" aria-hidden="true"></i><span>Feel supported<small>Your care team is near</small></span></strong>
    </div>
    <section class="features-section" id="features" aria-labelledby="features-title">
        <div class="section-heading">
            <div>
                <div class="eyebrow">Made for everyday care</div>
                <h2 id="features-title">Less to remember.<br>More peace of mind.</h2>
            </div>
            <p class="section-lead">Your <span>health information</span>, <span>appointments</span>, and <span>care team</span>, brought together in one simple space.</p>
        </div>
        <div class="feature-grid">
            <article class="feature-card">
                <div class="feature-icon"><i class="bi bi-heart-pulse" aria-hidden="true"></i></div>
                <h3>Follow your journey</h3>
                <p>Keep your pregnancy progress, cycle records, and health history together as your needs change.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon"><i class="bi bi-calendar2-check" aria-hidden="true"></i></div>
                <h3>Stay a step ahead</h3>
                <p>See upcoming checkups and receive reminders, so your next care appointment is easier to remember.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon"><i class="bi bi-chat-heart" aria-hidden="true"></i></div>
                <h3>Feel more supported</h3>
                <p>Connect with your health team and explore learning materials for every chapter of your health.</p>
            </article>
        </div>
    </section>
    <section class="care-gallery" aria-labelledby="gallery-title">
        <div class="care-gallery-copy">
            <div class="eyebrow">Care in your community</div>
            <h2 id="gallery-title">Real support, for every part of your journey.</h2>
            <p>From checkups and family planning to pregnancy support, ReproCare keeps you connected to the local health workers who care for you.</p>
            <a class="gallery-link" href="{{ route('register') }}">Join your care network <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="care-gallery-grid">
            <figure class="care-gallery-photo care-gallery-photo-large">
                <img src="{{ asset('images/maternal/landing-maternal-health.jpg') }}" alt="Community health worker supporting a woman at a maternal health station" loading="lazy">
                <figcaption><i class="bi bi-heart-pulse" aria-hidden="true"></i> Health support that listens</figcaption>
            </figure>
            <figure class="care-gallery-photo care-gallery-photo-small">
                <img src="{{ asset('images/maternal/home-visit.jpg') }}" alt="Health worker providing a prenatal check during a home visit" loading="lazy">
                <figcaption><i class="bi bi-house-heart" aria-hidden="true"></i> Care that reaches you</figcaption>
            </figure>
        </div>
    </section>
    <section class="how-section" id="how-it-works" aria-labelledby="how-title">
        <div><div class="eyebrow">A simple start</div><h2 id="how-title">Your next chapter<br>starts here.</h2><p>Start with your details, then let your local care team guide the rest.</p><a class="text-link" href="{{ route('register') }}">Create your account <span aria-hidden="true">&nbsp; &rarr;</span></a></div>
        <ol class="how-steps">
            <li><span class="step-number">01</span><h3>Tell us about you</h3><p>Create an account with your details and a valid ID.</p></li>
            <li><span class="step-number">02</span><h3>Meet your care network</h3><p>Your barangay health worker reviews your registration.</p></li>
            <li><span class="step-number">03</span><h3>Make yourself at home</h3><p>Once approved, log in to start managing your care.</p></li>
        </ol>
    </section>
</div>
@endsection
