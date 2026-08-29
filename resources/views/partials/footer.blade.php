<footer class="rp-footer mt-auto">
    <div class="container-rp mx-auto">
        <div class="row g-4 align-items-start">
            <div class="col-md-6">
                <a href="{{ route('home') }}" class="rp-brand mb-3">
                    @include('partials.brand-mark', ['size' => 32, 'tone' => 'light'])
                    <span class="rp-brand-word" style="color:#fff6e4;">
                        {{ $brand->siteName() }}
                        <span class="rp-brand-sub" style="color:#d4a017;">{{ $brand->tagline() }}</span>
                    </span>
                </a>
                <p class="text-body-md mb-0" style="color:#c9b89a;max-width:28rem;">
                    An internship portal that reads CVs the way a placement officer would — score the fit, keep the human decision.
                </p>
            </div>
            <div class="col-md-6">
                <div class="d-flex flex-wrap gap-3 justify-content-md-end">
                    <a href="{{ route('jobs.index') }}">Find jobs</a>
                    <a href="{{ route('login') }}">Sign in</a>
                    <a href="{{ route('login') }}#signup">Create account</a>
                    @auth
                        <a href="{{ route('privacy.index') }}">Privacy</a>
                    @endauth
                </div>
                <p class="text-body-md mt-3 mb-0 text-md-end" style="color:#c9b89a;">&copy; {{ date('Y') }} {{ $brand->siteName() }} Internship System.</p>
            </div>
        </div>
    </div>
</footer>
