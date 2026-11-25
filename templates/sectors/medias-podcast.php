<?php

/*
 * Pack « Médias et presse » — podcast : 8 pages, en français et en anglais.
 * Les épisodes sont les articles du blog (une page par épisode, catégories = séries),
 * les plateformes d'écoute, les saisons, l'équipe, les invités, le soutien des
 * auditeurs, les partenaires (audience en graphique) et le contact.
 * Les vidéos d'exemple sont des films libres de la Blender Foundation (CC BY), à remplacer.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Médias et presse', 'en' => 'Media and press'],
        'icon' => 'newspaper',
        'keywords' => ['média', 'medias', 'presse', 'journal', 'radio', 'télévision', 'podcast', 'balado', 'émission', 'journaliste', 'media', 'press', 'podcast', 'show', 'journalist'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $episodes = $u('/episodes', '/episodes');
        $seasons = $u('/saisons', '/seasons');
        $about = $u('/le-podcast', '/about');
        $guest = $u('/etre-invite', '/be-a-guest');
        $support = $u('/soutenir', '/support');
        $partners = $u('/partenaires', '/sponsors');
        $contact = '/contact';
        $post = fn (string $fr, string $en) => $episodes.'/'.$T($fr, $en);
        $cat = fn (string $fr, string $en) => $episodes.$u('/categorie/', '/category/').$T($fr, $en);
        $yt = fn (string $id) => 'https://www.youtube.com/watch?v='.$id;

        $platforms = [
            ['spotify', 'Spotify', 'https://open.spotify.com/'], ['youtube', 'YouTube', 'https://www.youtube.com/'], ['soundcloud', 'SoundCloud', 'https://soundcloud.com/'],
            ['whatsapp', $T('Chaîne WhatsApp', 'WhatsApp channel')], ['instagram', 'Instagram'], ['tiktok', 'TikTok'],
        ];
        $cta = ['title' => $T('Le podcast vit grâce à vous', 'The podcast lives thanks to you'), 'text' => $T('Gratuit et indépendant : soutenez-nous pour une saison de plus, ou faites connaître le podcast autour de vous.', 'Free and independent: support us for one more season, or tell people about the podcast.'),
            'buttons' => [[$T('Nous soutenir', 'Support us'), $support, 'accent'], [$T('Tous les épisodes', 'All episodes'), $episodes, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Le podcast des voix qui inspirent : entrepreneurs, artistes et citoyens racontent leur parcours. Un nouvel épisode chaque mardi.', 'The podcast of inspiring voices: entrepreneurs, artists and citizens tell their stories. A new episode every Tuesday.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Nouvel épisode chaque mardi', 'New episode every Tuesday'),
                    'title' => $T('Les voix qui changent les choses', 'The voices that change things'),
                    'text' => $T('Entrepreneurs, artistes, militants : chaque semaine, une conversation sans filtre avec celles et ceux qui font bouger le continent.', 'Entrepreneurs, artists, activists: every week, an unfiltered conversation with the people moving the continent forward.'),
                    'buttons' => [[$T('Écouter le dernier épisode', 'Listen to the latest episode'), $post('ep-42-entreprendre-sans-capital', 'ep-42-starting-up-with-no-capital')], [$T('Tous les épisodes', 'All episodes'), $episodes]],
                    'photo' => ['medias-podcast-accueil', 'mic', $T('Deux personnes enregistrant un podcast', 'Two people recording a podcast')],
                ]),
                M::platforms($theme, ['title' => $T('Disponible sur toutes vos plateformes', 'Available on all your platforms'), 'items' => $platforms]),
                M::posts($theme, ['eyebrow' => $T('Épisodes', 'Episodes'), 'title' => $T('Les derniers épisodes', 'Latest episodes'), 'count' => 3, 'more' => $T('Écouter', 'Listen')]),
                B::split($theme, [
                    'eyebrow' => $T('Le podcast', 'The podcast'),
                    'title' => $T('Une heure, une voix, une histoire', 'One hour, one voice, one story'),
                    'html' => '<p>'.e($T('Lancé dans une chambre avec un seul micro, le podcast réunit aujourd’hui des dizaines de milliers d’auditeurs. Le principe n’a pas changé : prendre le temps d’écouter des parcours qui inspirent.', 'Started in a bedroom with a single microphone, the podcast now brings together tens of thousands of listeners. The idea hasn’t changed: take the time to listen to inspiring journeys.')).'</p>',
                    'bullets' => [$T('Un épisode par semaine, le mardi', 'One episode a week, on Tuesdays'), $T('Version vidéo sur YouTube', 'Video version on YouTube'), $T('Transcription pour chaque épisode', 'A transcript for every episode')],
                    'buttons' => [[$T('Découvrir l’équipe', 'Meet the team'), $about, 'outline']],
                    'photo' => ['medias-podcast-studio', 'headphones', $T('Micro et casque dans un studio', 'Microphone and headphones in a studio')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                M::videos($theme, ['eyebrow' => $T('En vidéo', 'On video'), 'title' => $T('Regardez les épisodes', 'Watch the episodes'), 'intro' => $T('Les conversations filmées en studio, en intégralité.', 'Full studio conversations, on camera.'), 'items' => [
                    [$T('Épisode 42 — Entreprendre sans capital', 'Episode 42 — Starting up with no capital'), $yt('PVGeM40dABA'), '3:05'],
                    [$T('Épisode 41 — La rumba, patrimoine vivant', 'Episode 41 — Rumba, a living heritage'), $yt('WhWc3b3KhnY'), '7:44'],
                    [$T('Épisode 40 — Coder depuis Kigali', 'Episode 40 — Coding from Kigali'), $yt('R6MlUcmOul8'), '12:14'],
                ]]),
                B::testimonials($theme, ['eyebrow' => $T('Auditeurs', 'Listeners'), 'title' => $T('Ce que disent les auditeurs', 'What listeners say'), 'rating' => 5, 'items' => [
                    [$T('Mon rendez-vous du mardi. Chaque épisode me donne une idée à appliquer dans mon entreprise.', 'My Tuesday ritual. Every episode gives me an idea to use in my business.'), '[Prénom Nom]', $T('Auditrice depuis la saison 1', 'Listener since season 1')],
                    [$T('Des invités qu’on n’entend nulle part ailleurs, et des questions qui vont au fond des choses.', 'Guests you don’t hear anywhere else, and questions that go deep.'), '[Prénom Nom]', $T('Auditeur', 'Listener')],
                    [$T('J’écoute dans les embouteillages : une heure qui passe en dix minutes.', 'I listen in traffic jams: an hour that flies by in ten minutes.'), '[Prénom Nom]', $T('Auditrice', 'Listener')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'episodes', 'slug' => $u('episodes', 'episodes'), 'title' => $T('Épisodes', 'Episodes'),
            'description' => $T('Tous les épisodes du podcast, par série : entreprendre, culture, société et coulisses.', 'Every episode of the podcast, by series: business, culture, society and behind the scenes.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Épisodes', 'Episodes'), 'title' => $T('Tous les épisodes', 'All episodes'), 'intro' => $T('Notes, invités et liens d’écoute de chaque épisode.', 'Notes, guests and listening links for every episode.')]),
                M::terms($theme, ['bg' => 'light']),
                M::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Écouter', 'Listen')]),
                M::platforms($theme, ['title' => $T('Abonnez-vous pour ne rien manquer', 'Subscribe so you never miss one'), 'items' => $platforms]),
            ]];

        $pages[] = ['key' => 'seasons', 'slug' => $u('saisons', 'seasons'), 'title' => $T('Saisons et séries', 'Seasons and series'),
            'description' => $T('Les séries du podcast et les saisons passées.', 'The podcast’s series and past seasons.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Séries', 'Series'), 'title' => $T('Quatre séries, quatre façons d’écouter', 'Four series, four ways to listen'), 'intro' => $T('Choisissez votre porte d’entrée.', 'Choose where to start.')]),
                B::features($theme, ['cols' => 4, 'items' => [
                    ['rocket', $T('Entreprendre', 'Business'), $T('Fondateurs et fondatrices racontent leurs débuts, leurs échecs et leurs réussites.', 'Founders tell of their beginnings, failures and successes.'), $cat('entreprendre', 'business'), $T('Écouter', 'Listen')],
                    ['palette', 'Culture', $T('Musiciens, écrivains, cinéastes : la création en conversation.', 'Musicians, writers, filmmakers: creativity in conversation.'), $cat('culture', 'culture'), $T('Écouter', 'Listen')],
                    ['users', $T('Société', 'Society'), $T('Éducation, santé, climat : des citoyens qui agissent.', 'Education, health, climate: citizens taking action.'), $cat('societe', 'society'), $T('Écouter', 'Listen')],
                    ['headphones', $T('Coulisses', 'Behind the scenes'), $T('Questions d’auditeurs, épisodes spéciaux et vie du podcast.', 'Listener questions, special episodes and podcast life.'), $cat('coulisses', 'behind-the-scenes'), $T('Écouter', 'Listen')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Saisons', 'Seasons'), 'title' => $T('Les saisons précédentes', 'Past seasons'), 'bg' => 'light', 'items' => [
                    ['medias-podcast-saison-1', 'mic', $T('Saison 1 — Les pionniers', 'Season 1 — The pioneers'), $T('12 épisodes avec celles et ceux qui ont ouvert la voie.', '12 episodes with the people who led the way.')],
                    ['medias-podcast-saison-2', 'mic', $T('Saison 2 — Made in Africa', 'Season 2 — Made in Africa'), $T('Artisans, industriels et marques qui produisent sur le continent.', 'Artisans, manufacturers and brands producing on the continent.')],
                    ['medias-podcast-saison-3', 'mic', $T('Saison 3 — La relève', 'Season 3 — The next generation'), $T('Les moins de 30 ans qui bousculent leur secteur.', 'Under-30s shaking up their sector.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('le-podcast', 'about'), 'title' => $T('Le podcast', 'About'),
            'description' => $T('L’histoire du podcast, l’équipe qui le produit et nos chiffres d’écoute.', 'The story of the podcast, the team behind it and our listening figures.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le podcast', 'About'), 'title' => $T('Une conversation qui dure depuis 2021', 'A conversation going since 2021'), 'intro' => $T('Indépendant, gratuit, produit avec soin.', 'Independent, free, carefully produced.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Un micro, une chambre, une idée', 'One microphone, one bedroom, one idea'),
                    'html' => '<p>'.e($T('Tout est parti d’une frustration : on parlait trop peu de celles et ceux qui réussissent ici, avec peu de moyens. Le premier épisode a été enregistré avec un seul micro, dans une chambre.', 'It all started with frustration: too little was said about the people succeeding here, with few resources. The first episode was recorded with a single microphone, in a bedroom.')).'</p><p>'.e($T('Quatre saisons plus tard, le podcast a son studio, une petite équipe et une communauté d’auditeurs fidèles dans plus de 40 pays.', 'Four seasons later, the podcast has its own studio, a small team and a loyal community of listeners in over 40 countries.')).'</p>',
                    'photo' => ['medias-podcast-histoire', 'mic', $T('Animatrice de podcast au micro', 'Podcast host at the microphone')],
                ]),
                B::stats($theme, ['items' => [
                    [42, '', $T('épisodes publiés', 'episodes published')],
                    [1.2, ' M', $T('écoutes au total', 'total plays')],
                    [40, '+', $T('pays d’écoute', 'listening countries')],
                    [4.9, '/5', $T('note moyenne', 'average rating')],
                ]]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Derrière le micro', 'Behind the microphone'), 'shape' => 'circle', 'items' => [
                    ['[Prénom Nom]', $T('Créatrice et animatrice', 'Creator and host'), 'medias-podcast-equipe-1'],
                    ['[Prénom Nom]', $T('Co-animateur', 'Co-host'), 'medias-podcast-equipe-2'],
                    ['[Prénom Nom]', $T('Réalisation et montage', 'Producer and editor'), 'medias-podcast-equipe-3'],
                    ['[Prénom Nom]', $T('Recherche et invités', 'Research and bookings'), 'medias-podcast-equipe-4'],
                ]]),
                M::timeline($theme, ['eyebrow' => $T('Repères', 'Milestones'), 'title' => $T('Quatre saisons', 'Four seasons'), 'bg' => 'light', 'items' => [
                    ['2021', $T('Épisode 1', 'Episode 1'), $T('Enregistré dans une chambre, écouté par 200 personnes.', 'Recorded in a bedroom, heard by 200 people.')],
                    ['2022', $T('Premier studio', 'First studio'), $T('Un vrai studio et la version vidéo sur YouTube.', 'A real studio and the video version on YouTube.')],
                    ['2023', $T('Un million d’écoutes', 'A million plays'), $T('Et une première rencontre en public.', 'And a first live recording.')],
                    ['2025', $T('Saison 4', 'Season 4'), $T('Quatre séries et des épisodes en anglais.', 'Four series and episodes in English.')],
                ]]),
            ]];

        $pages[] = ['key' => 'guest', 'slug' => $u('etre-invite', 'be-a-guest'), 'title' => $T('Être invité', 'Be a guest'),
            'description' => $T('Proposez votre histoire ou celle de quelqu’un qui vous inspire : comment se passe un enregistrement.', 'Suggest your story or that of someone who inspires you: how a recording works.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Invités', 'Guests'), 'title' => $T('Votre histoire mérite d’être entendue', 'Your story deserves to be heard'), 'intro' => $T('Nous cherchons des parcours sincères, pas des discours.', 'We are looking for honest journeys, not speeches.')]),
                B::steps($theme, ['title' => $T('Comment ça se passe', 'How it works'), 'items' => [
                    [$T('Proposition', 'Suggestion'), $T('Vous nous écrivez : qui, quel parcours, pourquoi maintenant.', 'You write to us: who, what journey, why now.')],
                    [$T('Appel préparatoire', 'Prep call'), $T('Trente minutes pour faire connaissance et préparer les thèmes.', 'Thirty minutes to get acquainted and prepare the topics.')],
                    [$T('Enregistrement', 'Recording'), $T('Une heure en studio ou à distance, dans une ambiance détendue.', 'An hour in the studio or remotely, in a relaxed atmosphere.')],
                    [$T('Diffusion', 'Release'), $T('Vous validez les éléments sensibles avant la mise en ligne.', 'You approve sensitive points before release.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('En studio ou à distance', 'In the studio or remotely'),
                    'title' => $T('Pas besoin d’être un expert du micro', 'No need to be a microphone pro'),
                    'html' => '<p>'.e($T('Notre équipe vous guide du début à la fin. À distance, nous vous envoyons un micro si nécessaire. Le montage enlève les hésitations, jamais le sens.', 'Our team guides you from start to finish. For remote recordings, we can send you a microphone. Editing removes hesitations, never meaning.')).'</p>',
                    'bullets' => [$T('Studio climatisé et insonorisé', 'Air-conditioned, soundproof studio'), $T('Enregistrement à distance possible', 'Remote recording possible'), $T('Photos et extraits vidéo offerts', 'Photos and video clips provided')],
                    'buttons' => [[$T('Proposer un invité', 'Suggest a guest'), $contact]],
                    'photo' => ['medias-podcast-invite', 'mic', $T('Invité souriant pendant un enregistrement', 'Smiling guest during a recording')],
                    'bg' => 'light',
                ]),
                B::faq($theme, ['title' => $T('Questions des futurs invités', 'Questions from future guests'), 'items' => [
                    [$T('Faut-il payer pour être invité ?', 'Do I have to pay to be a guest?'), $T('Jamais. Les invités ne paient pas et ne sont pas rémunérés : c’est une conversation, pas une publicité.', 'Never. Guests don’t pay and aren’t paid: it is a conversation, not an advert.')],
                    [$T('Puis-je relire l’épisode ?', 'Can I review the episode?'), $T('Vous pouvez signaler une erreur factuelle ou une information sensible avant la diffusion.', 'You can flag a factual error or sensitive information before release.')],
                    [$T('Quelles langues ?', 'Which languages?'), $T('Français et anglais ; d’autres langues avec sous-titres pour la vidéo.', 'French and English; other languages with subtitles for the video.')],
                ]]),
            ]];

        $pages[] = ['key' => 'support', 'slug' => $u('soutenir', 'support'), 'title' => $T('Nous soutenir', 'Support us'),
            'description' => $T('Soutenez le podcast : adhésion mensuelle, don ponctuel ou bouche-à-oreille.', 'Support the podcast: monthly membership, one-off donation or word of mouth.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nous soutenir', 'Support us'), 'title' => $T('Gardez le podcast libre et gratuit', 'Keep the podcast free'), 'intro' => $T('Vos contributions financent le studio, le montage et les déplacements.', 'Your contributions fund the studio, editing and travel.')]),
                B::donate($theme, [
                    'eyebrow' => $T('Objectif saison 5', 'Season 5 goal'),
                    'title' => $T('Financer une saison de plus', 'Fund one more season'),
                    'text' => $T('Douze épisodes, quatre reportages en région et la transcription de chaque épisode : voici ce que votre soutien rend possible.', 'Twelve episodes, four regional reports and a transcript for every episode: that is what your support makes possible.'),
                    'goal' => $T('Objectif atteint', 'Goal reached'), 'percent' => 64,
                    'amounts' => ['5 $', '10 $', '25 $', '50 $'],
                    'button' => $T('Je soutiens', 'I support'), 'url' => $contact,
                    'photo' => ['medias-podcast-soutien', 'heart', $T('Équipe du podcast en enregistrement', 'Podcast team recording')],
                    'anchor' => 'soutenir',
                    'bg' => 'white',
                ]),
                B::pricing($theme, ['eyebrow' => $T('Adhésion', 'Membership'), 'title' => $T('Devenez membre', 'Become a member'), 'url' => $contact, 'items' => [
                    [$T('Auditeur', 'Listener'), '3', '$', $T('/ mois', '/ month'), $T('Pour dire merci, chaque mois.', 'To say thank you, every month.'), [$T('Votre nom dans les remerciements', 'Your name in the credits'), $T('Lettre des coulisses', 'Behind-the-scenes newsletter'), '-'.$T('Épisodes en avant-première', 'Early access to episodes'), '-'.$T('Rencontre annuelle', 'Annual meetup')], $T('Adhérer', 'Join')],
                    [$T('Ambassadeur', 'Ambassador'), '8', '$', $T('/ mois', '/ month'), $T('Pour les fidèles du mardi.', 'For the Tuesday regulars.'), [$T('Tous les avantages Auditeur', 'All Listener perks'), $T('Épisodes en avant-première', 'Early access to episodes'), $T('Questions posées aux invités', 'Questions put to guests'), '-'.$T('Rencontre annuelle', 'Annual meetup')], $T('Adhérer', 'Join'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Mécène', 'Patron'), '25', '$', $T('/ mois', '/ month'), $T('Pour faire grandir le podcast.', 'To help the podcast grow.'), [$T('Tous les avantages Ambassadeur', 'All Ambassador perks'), $T('Invitation à la rencontre annuelle', 'Invitation to the annual meetup'), $T('Mention dans un épisode', 'Shout-out in an episode'), $T('Échange avec l’équipe', 'Chat with the team')], $T('Adhérer', 'Join')],
                ]]),
                B::faq($theme, ['title' => $T('Vos questions', 'Your questions'), 'bg' => 'light', 'items' => [
                    [$T('Comment contribuer ?', 'How do I contribute?'), $T('Par Mobile Money, carte ou plateforme d’adhésion : écrivez-nous pour recevoir le lien.', 'By Mobile Money, card or membership platform: write to us for the link.')],
                    [$T('Puis-je arrêter quand je veux ?', 'Can I stop whenever I want?'), $T('Oui, l’adhésion est sans engagement.', 'Yes, membership has no commitment.')],
                    [$T('Je ne peux pas donner, comment aider ?', 'I can’t give money, how can I help?'), $T('Partagez un épisode, laissez une note sur votre plateforme : cela compte énormément.', 'Share an episode, leave a rating on your platform: it means a lot.')],
                ]]),
            ]];

        $pages[] = ['key' => 'sponsors', 'slug' => $u('partenaires', 'sponsors'), 'title' => $T('Partenaires', 'Sponsors'),
            'description' => $T('Devenez partenaire du podcast : mentions, épisodes sponsorisés et séries. Audience et offres.', 'Sponsor the podcast: mentions, sponsored episodes and series. Audience and offers.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Partenaires', 'Sponsors'), 'title' => $T('Associez votre marque à des voix qui inspirent', 'Pair your brand with inspiring voices'), 'intro' => $T('Une audience attentive, qui écoute jusqu’au bout.', 'An attentive audience that listens to the end.')]),
                M::chart($theme, [
                    'eyebrow' => $T('Audience', 'Audience'),
                    'title' => $T('Des écoutes en hausse, saison après saison', 'Plays growing, season after season'),
                    'html' => '<p>'.e($T('Écoutes mensuelles cumulées sur toutes les plateformes (statistiques des plateformes, [année]). 78 % des auditeurs écoutent plus de la moitié de chaque épisode.', 'Monthly plays across all platforms (platform statistics, [year]). 78% of listeners hear more than half of each episode.')).'</p>',
                    'bullets' => [$T('Auditeurs de 25 à 40 ans à 65 %', '65% of listeners aged 25 to 40'), $T('Entrepreneurs et cadres', 'Entrepreneurs and professionals'), $T('Écoute en Afrique et dans la diaspora', 'Listening across Africa and the diaspora')],
                    'chart' => ['kind' => 'line', 'title' => $T('Écoutes par mois (milliers)', 'Plays per month (thousands)'), 'series' => [$T('Audio', 'Audio'), $T('Vidéo', 'Video')], 'rows' => [
                        [$T('Jan', 'Jan'), 32, 12], [$T('Fév', 'Feb'), 35, 14], [$T('Mar', 'Mar'), 41, 17], [$T('Avr', 'Apr'), 44, 21], [$T('Mai', 'May'), 52, 24], [$T('Juin', 'Jun'), 58, 29],
                    ]],
                ]),
                B::pricing($theme, ['eyebrow' => $T('Offres', 'Offers'), 'title' => $T('Nos formats de partenariat', 'Our sponsorship formats'), 'intro' => $T('Toujours annoncés comme tels : nos auditeurs savent qui nous soutient.', 'Always disclosed: our listeners know who supports us.'), 'url' => $contact, 'bg' => 'light', 'items' => [
                    [$T('Mention', 'Mention'), '150', '$', $T('/ épisode', '/ episode'), $T('30 secondes lues par l’animatrice en début d’épisode.', '30 seconds read by the host at the start of an episode.'), [$T('Texte écrit avec vous', 'Script written with you'), $T('Lien dans les notes', 'Link in the show notes'), $T('Statistiques d’écoute', 'Listening statistics'), '-'.$T('Version vidéo', 'Video version')], $T('Réserver', 'Book')],
                    [$T('Épisode présenté par', 'Presented by'), '500', '$', $T('/ épisode', '/ episode'), $T('Votre marque associée à un épisode entier.', 'Your brand tied to a full episode.'), [$T('Mentions début et fin', 'Opening and closing mentions'), $T('Logo sur la vidéo', 'Logo on the video'), $T('Publication sur nos réseaux', 'Post on our networks'), $T('Statistiques détaillées', 'Detailed statistics')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus demandé', 'Most requested')],
                    [$T('Série partenaire', 'Partner series'), '3000', '$', $T('/ série', '/ series'), $T('Une mini-série de 4 épisodes sur un thème commun.', 'A 4-episode mini-series on a shared theme.'), [$T('Ligne éditoriale indépendante', 'Independent editorial line'), $T('Mention « en partenariat avec »', '“In partnership with” label'), $T('Événement de lancement', 'Launch event'), $T('Bilan complet', 'Full report')], $T('Nous contacter', 'Contact us')],
                ]]),
                B::cta($theme, ['title' => $T('Parlons de votre projet', 'Let’s talk about your project'), 'text' => $T('Nous ne travaillons qu’avec des marques cohérentes avec nos valeurs. Présentez-nous la vôtre.', 'We only work with brands that fit our values. Tell us about yours.'), 'buttons' => [[$T('Devenir partenaire', 'Become a sponsor'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Écrivez à l’équipe du podcast : invités, partenariats, questions d’auditeurs.', 'Write to the podcast team: guests, sponsorships, listener questions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Écrivez-nous', 'Write to us'), 'intro' => $T('Une question pour un épisode « Coulisses », une idée d’invité, un partenariat ?', 'A question for a “Behind the scenes” episode, a guest idea, a sponsorship?')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['message-circle', $T('Questions d’auditeurs', 'Listener questions'), $T('Nous y répondons dans les épisodes « Coulisses ».', 'We answer them in our “Behind the scenes” episodes.')],
                    ['mic', $T('Proposer un invité', 'Suggest a guest'), $T('Dites-nous qui, et pourquoi son histoire compte.', 'Tell us who, and why their story matters.'), $guest, $T('Comment ça marche', 'How it works')],
                    ['handshake', $T('Partenariats', 'Sponsorships'), $T('Mentions, épisodes et séries partenaires.', 'Mentions, episodes and partner series.'), $partners, $T('Voir les offres', 'See the offers')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Le studio', 'The studio'), 'intro' => $T('Enregistrements sur rendez-vous uniquement.', 'Recordings by appointment only.'),
                    'items' => [
                        ['map-pin', $T('Kigali, Rwanda', 'Kigali, Rwanda')],
                        ['mail', 'bonjour@exemple.org', 'mailto:bonjour@exemple.org'],
                        ['phone', '+250 000 000 000', 'tel:+250000000000'],
                        ['clock', $T('Nouvel épisode chaque mardi à 6 h', 'New episode every Tuesday at 6 am')],
                    ],
                    'social' => ['spotify', 'youtube', 'instagram', 'tiktok'],
                    'subject' => true,
                ]),
            ]];

        $blog = [
            'base' => $u('episodes', 'episodes'),
            'title' => $T('Épisodes', 'Episodes'),
            'layout' => ['share' => true, 'terms' => true],
            'interval' => 7,
            'categories' => ['entreprendre' => $T('Entreprendre', 'Business'), 'culture' => 'Culture', 'societe' => $T('Société', 'Society'), 'coulisses' => $T('Coulisses', 'Behind the scenes')],
            'posts' => [
                ['key' => 'ep42', 'slug' => $u('ep-42-entreprendre-sans-capital', 'ep-42-starting-up-with-no-capital'), 'category' => 'entreprendre', 'tags' => [$T('Financement', 'Funding'), $T('Commerce', 'Retail')],
                    'title' => $T('Ép. 42 — Entreprendre sans capital', 'Ep. 42 — Starting up with no capital'),
                    'excerpt' => $T('Partie de rien, [Prénom Nom] dirige aujourd’hui une chaîne de boutiques. Elle raconte ses débuts au marché.', 'Starting from nothing, [First Last] now runs a chain of shops. She tells us about her early days at the market.'),
                    'photo' => ['medias-podcast-episode-42', 'rocket', $T('Entrepreneure dans sa boutique', 'Entrepreneur in her shop')],
                    'heading' => $T('Dans cet épisode', 'In this episode'),
                    'quote' => $T('« Mon premier capital, c’était la confiance de mes clientes. »', '“My first capital was my customers’ trust.”'),
                    'body' => [
                        $T('Elle a commencé avec une table au marché et trois pagnes achetés à crédit. Quinze ans plus tard, elle emploie quarante personnes.', 'She started with a market table and three wraps bought on credit. Fifteen years later, she employs forty people.'),
                        $T('Dans cet épisode : la tontine qui a financé sa première boutique, l’erreur qui a failli tout arrêter, et ses conseils pour vendre sur les réseaux sociaux.', 'In this episode: the savings group that funded her first shop, the mistake that nearly ended it all, and her tips for selling on social media.'),
                        $T('Écoutez l’épisode sur votre plateforme préférée ou regardez-le en vidéo. Transcription disponible sur demande.', 'Listen on your favorite platform or watch it on video. Transcript available on request.'),
                    ]],
                ['key' => 'ep41', 'slug' => $u('ep-41-la-rumba-patrimoine-vivant', 'ep-41-rumba-living-heritage'), 'category' => 'culture', 'tags' => [$T('Musique', 'Music'), $T('Patrimoine', 'Heritage')],
                    'title' => $T('Ép. 41 — La rumba, patrimoine vivant', 'Ep. 41 — Rumba, a living heritage'),
                    'excerpt' => $T('Un guitariste de légende et une jeune chanteuse dialoguent sur l’avenir de la rumba.', 'A legendary guitarist and a young singer talk about the future of rumba.'),
                    'photo' => ['medias-podcast-episode-41', 'music', $T('Guitariste jouant sur scène', 'Guitarist playing on stage')],
                    'body' => [
                        $T('Deux générations, une même passion. Dans cet épisode, nous avons réuni un guitariste qui a accompagné les plus grands et une chanteuse de 24 ans qui réinvente le genre.', 'Two generations, one passion. In this episode, we brought together a guitarist who played with the greats and a 24-year-old singer reinventing the genre.'),
                        $T('Ils parlent transmission, droits d’auteur, plateformes de streaming et de ce que signifie l’inscription de la rumba au patrimoine de l’humanité.', 'They talk about passing on skills, copyright, streaming platforms and what rumba’s listing as world heritage means.'),
                        $T('Bonus : une session acoustique enregistrée en fin d’épisode.', 'Bonus: an acoustic session recorded at the end of the episode.'),
                    ]],
                ['key' => 'ep40', 'slug' => $u('ep-40-coder-depuis-kigali', 'ep-40-coding-from-kigali'), 'category' => 'entreprendre', 'tags' => ['Tech', $T('Jeunesse', 'Youth')],
                    'title' => $T('Ép. 40 — Coder depuis Kigali', 'Ep. 40 — Coding from Kigali'),
                    'excerpt' => $T('Trois développeuses racontent comment elles travaillent pour des entreprises du monde entier.', 'Three developers explain how they work for companies around the world.'),
                    'photo' => ['medias-podcast-episode-40', 'laptop', $T('Développeuse devant son ordinateur', 'Developer at her computer')],
                    'body' => [
                        $T('Elles ont appris à coder en ligne, parfois sur un téléphone. Aujourd’hui, elles travaillent à distance pour des start-up d’Europe et d’Amérique.', 'They learned to code online, sometimes on a phone. Today they work remotely for start-ups in Europe and America.'),
                        $T('Salaires, connexion Internet, décalage horaire, sentiment d’isolement : elles parlent de tout, sans détour.', 'Salaries, Internet access, time zones, isolation: they talk about everything, candidly.'),
                        $T('Les ressources citées dans l’épisode sont listées dans les notes.', 'Resources mentioned in the episode are listed in the notes.'),
                    ]],
                ['key' => 'ep39', 'slug' => $u('ep-39-enseigner-autrement', 'ep-39-teaching-differently'), 'category' => 'societe', 'tags' => [$T('Éducation', 'Education')],
                    'title' => $T('Ép. 39 — Enseigner autrement', 'Ep. 39 — Teaching differently'),
                    'excerpt' => $T('Un instituteur de village a doublé le taux de réussite de sa classe. Sa méthode tient en trois idées.', 'A village teacher doubled his class’s pass rate. His method comes down to three ideas.'),
                    'photo' => ['medias-podcast-episode-39', 'graduation-cap', $T('Enseignant devant sa classe', 'Teacher in front of his class')],
                    'body' => [
                        $T('Quatre-vingts élèves, une seule salle, peu de manuels. Pourtant, sa classe obtient chaque année des résultats parmi les meilleurs de la province.', 'Eighty pupils, one room, few textbooks. Yet every year his class achieves some of the best results in the province.'),
                        $T('Travail en petits groupes, élèves tuteurs, parents associés : il détaille une méthode simple, que d’autres écoles commencent à adopter.', 'Small-group work, peer tutors, involved parents: he details a simple method that other schools are starting to adopt.'),
                        $T('Un épisode à partager avec tous les enseignants que vous connaissez.', 'An episode to share with every teacher you know.'),
                    ]],
                ['key' => 'ep38', 'slug' => $u('ep-38-vos-questions', 'ep-38-your-questions'), 'category' => 'coulisses', 'tags' => [$T('Auditeurs', 'Listeners')],
                    'title' => $T('Ép. 38 — Vos questions, nos réponses', 'Ep. 38 — Your questions, our answers'),
                    'excerpt' => $T('Comment choisissons-nous les invités ? Combien coûte un épisode ? L’équipe répond à tout.', 'How do we choose guests? How much does an episode cost? The team answers everything.'),
                    'photo' => ['medias-podcast-episode-38', 'message-circle', $T('Équipe du podcast autour d’une table', 'Podcast team around a table')],
                    'body' => [
                        $T('Vous nous avez envoyé plus de 300 questions. Pour cet épisode spécial, toute l’équipe prend le micro.', 'You sent us more than 300 questions. For this special episode, the whole team takes the microphone.'),
                        $T('Au programme : comment nous choisissons les invités, ce que coûte un épisode, nos erreurs du début et nos projets pour la saison 5.', 'On the agenda: how we pick guests, what an episode costs, our early mistakes and our plans for season 5.'),
                        $T('Merci pour votre fidélité. Continuez à nous écrire !', 'Thank you for your loyalty. Keep writing to us!'),
                    ]],
                ['key' => 'ep37', 'slug' => $u('ep-37-climat-les-villes-qui-agissent', 'ep-37-climate-cities-taking-action'), 'category' => 'societe', 'tags' => [$T('Climat', 'Climate'), $T('Ville', 'City')],
                    'title' => $T('Ép. 37 — Climat : les villes qui agissent', 'Ep. 37 — Climate: the cities taking action'),
                    'excerpt' => $T('Une urbaniste explique comment des quartiers se protègent des inondations avec des solutions simples.', 'An urban planner explains how neighborhoods protect themselves from floods with simple solutions.'),
                    'photo' => ['medias-podcast-episode-37', 'leaf', $T('Quartier arboré d’une ville africaine', 'Tree-lined neighborhood of an African city')],
                    'body' => [
                        $T('Chaque saison des pluies, des quartiers entiers sont inondés. Pourtant, des solutions existent, souvent peu coûteuses.', 'Every rainy season, entire neighborhoods flood. Yet solutions exist, often inexpensive ones.'),
                        $T('Notre invitée, urbaniste, a accompagné des comités de quartier pour planter, drainer et construire autrement.', 'Our guest, an urban planner, has helped neighborhood committees plant, drain and build differently.'),
                        $T('Elle partage les exemples qui marchent et les erreurs à éviter.', 'She shares the examples that work and the mistakes to avoid.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Épisodes', 'Episodes'), 'url' => $episodes],
                    ['label' => $T('Séries', 'Series'), 'url' => $seasons],
                    ['label' => $T('Le podcast', 'About'), 'url' => $about],
                    ['label' => $T('Être invité', 'Be a guest'), 'url' => $guest],
                    ['label' => $T('Partenaires', 'Sponsors'), 'url' => $partners],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Nous soutenir', 'Support us'),
                'cta_url' => $support,
                'footer_text' => $T('Podcast indépendant : chaque mardi, une conversation avec celles et ceux qui font bouger le continent.', 'Independent podcast: every Tuesday, a conversation with the people moving the continent forward.'),
                'footer_links' => [
                    ['label' => $T('Tous les épisodes', 'All episodes'), 'url' => $episodes],
                    ['label' => $T('Nous soutenir', 'Support us'), 'url' => $support],
                    ['label' => $T('Partenaires', 'Sponsors'), 'url' => $partners],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
