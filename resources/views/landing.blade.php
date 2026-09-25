@extends('layouts.public')

@section('navigation')
<div class="nav-account">
    <a href="{{ route('login') }}">Log in</a>
    <a class="button button-primary button-small" href="{{ route('register') }}">Get started</a>
</div>
@endsection

@section('content')
<div class="public-container">
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-copy">
            <div class="eyebrow">With you, every step of the way</div>
            <h1 id="hero-title">A little guidance.<br>A lot of care.<br><span>All for you.</span></h1>
            <p>From everyday wellness to your pregnancy journey, stay connected to the care you need and the people who are here for you.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="{{ route('register') }}">Start your care journey</a>
                <a class="button button-secondary" href="#features">Explore ReproCare</a>
            </div>
        </div>
        <div class="hero-visual">
            <img class="hero-photo" src="{{ asset('images/community-maternal-health-education.jpg') }}" alt="Pregnant women attending a community maternal health education session" width="630" height="420" fetchpriority="high">
            <div class="photo-label"><i class="bi bi-geo-alt" aria-hidden="true"></i> Care, closer to home</div>
            <div class="care-note">
                <div><strong>Your journey. Our shared care.</strong><small>Connected to your community health team</small></div>
            </div>
        </div>
    </section>
    <div class="community-strip" aria-label="What ReproCare does for you">
        <span>What you get</span>
        <strong><i class="bi bi-calendar2-check" aria-hidden="true"></i> Free prenatal checkups</strong>
        <strong><i class="bi bi-bell" aria-hidden="true"></i> Checkup reminders by SMS</strong>
        <strong><i class="bi bi-droplet" aria-hidden="true"></i> Period &amp; cycle tracking</strong>
        <strong><i class="bi bi-book" aria-hidden="true"></i> Maternal health learning</strong>
    </div>
    <section class="features-section" id="features" aria-labelledby="features-title">
        <div class="section-heading">
            <div><h2 id="features-title">Less to keep track of.<br>More peace of mind.</h2></div>
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
    <section class="how-section" id="how-it-works" aria-labelledby="how-title">
        <div><div class="eyebrow">A simple start</div><h2 id="how-title">Your next chapter<br>starts here.</h2><a class="text-link" href="{{ route('register') }}">Create your account <span aria-hidden="true">&nbsp; &rarr;</span></a></div>
        <ol class="how-steps">
            <li><span class="step-number">01</span><h3>Tell us about you</h3><p>Create an account with your details and a valid ID.</p></li>
            <li><span class="step-number">02</span><h3>Meet your care network</h3><p>Your barangay health worker reviews your registration.</p></li>
            <li><span class="step-number">03</span><h3>Make yourself at home</h3><p>Once approved, log in to start managing your care.</p></li>
        </ol>
    </section>
</div>
@endsection
