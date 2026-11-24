{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>{{ $title }}</title>
    <link>{{ $link }}</link>
    <atom:link href="{{ $self }}" rel="self" type="application/rss+xml"/>
    <description>{{ \App\Models\Setting::read('site_tagline') ?: $title }}</description>
    <language>{{ $locale }}</language>
@foreach ($posts as $post)
    <item>
        <title>{{ $post->title }}</title>
        <link>{{ $post->url() }}</link>
        <guid isPermaLink="true">{{ $post->url() }}</guid>
        @if ($post->published_at)<pubDate>{{ $post->published_at->toRssString() }}</pubDate>@endif
        <description>{{ $post->summary(300) }}</description>
    </item>
@endforeach
</channel>
</rss>
