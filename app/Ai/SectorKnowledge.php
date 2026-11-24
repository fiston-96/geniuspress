<?php

namespace App\Ai;

/**
 * Connaissances de l'assistant IA par secteur : public, ton, informations
 * indispensables d'un bon site, règles à respecter et ce qu'il ne faut jamais
 * inventer. Utilisées pour proposer des modèles et pour en rédiger les textes.
 * Les clés sont celles des packs de modèles (templates/sectors).
 */
final class SectorKnowledge
{
    /** Règles valables pour tous les secteurs (consignes en anglais pour le modèle). */
    public const COMMON = <<<'TXT'
        Writing rules for every website:
        - Use ONLY facts given by the site owner. Never invent names of people, testimonials attributed to real people, figures, statistics, prices, dates, addresses, phone numbers, e-mails, partners, awards, accreditations or legal mentions.
        - When a fact is needed but unknown, keep or write a short placeholder in square brackets in the page language, e.g. [à compléter], [Prénom Nom], [to be completed].
        - Sample testimonials stay clearly marked as placeholders ([Prénom Nom]); you may rephrase them generically.
        - Plain, concrete, warm language; short sentences; no jargon; inclusive and respectful of local culture.
        - Keep the role and approximate length of each text so the layout stays balanced (headings short, buttons 1–4 words).
        TXT;

    public const SECTORS = [
        'ong' => <<<'TXT'
            Sector: NGO / nonprofit / association. Audiences: donors, institutional partners, volunteers, beneficiaries, media.
            Must show: mission and the problem addressed, programs and where they run, measurable results, transparency (how funds are used, reports), clear ways to help (donate, volunteer, partner), contacts.
            Tone: human, hopeful, credible, never pitiful; show beneficiaries with dignity.
            Never invent: impact figures, partner organisations, registration numbers, donation channels (Mobile Money numbers, bank details).
            TXT,
        'eglise' => <<<'TXT'
            Sector: church / faith community. Audiences: members, newcomers and visitors, families, youth.
            Must show: service times and place, welcome for first-time visitors, ministries (children, youth, women, men, worship, outreach), events, teachings, how to give, contacts.
            Tone: warm, welcoming, respectful; stay faithful to the denomination described by the owner; keep any Bible verse accurate with its reference, or leave a placeholder.
            Never invent: pastor names, service times, denomination, doctrinal statements, giving channels.
            TXT,
        'ecole' => <<<'TXT'
            Sector: school / college / university. Audiences: parents, students, prospective students, staff.
            Must show: levels and programs, educational approach, admissions process and calendar, fees (or how to obtain them), facilities and school life, staff, news, contacts and office hours.
            Tone: reassuring, structured, ambitious, child-friendly for primary schools.
            Never invent: accreditation or approval numbers, exam pass rates, fees, diplomas awarded, staff names.
            TXT,
        'gouvernement' => <<<'TXT'
            Sector: municipality / public administration / government agency. Audiences: residents, businesses, visitors, partners.
            Must show: services and procedures (step by step, documents required, where and when), opening hours, news and official notices, projects, organisation, contacts.
            Tone: neutral, clear, accessible, strictly non-partisan; plain language for all citizens.
            Never invent: officials' names, laws or decree numbers, fees, deadlines, statistics, project budgets.
            TXT,
        'hopital' => <<<'TXT'
            Sector: hospital / clinic / health center. Audiences: patients and families, referring doctors, partners.
            Must show: emergency information first (how to reach the emergency department, phone placeholder), departments and services, doctors and specialties, appointments, practical information (hours, admission, insurance/payment), prevention advice, contacts.
            Tone: reassuring, precise, compassionate; respect confidentiality.
            Never: give medical diagnoses, dosages or cure promises; invent doctors, specialties, equipment, success rates, prices or accreditations. Always advise calling the emergency number for urgent cases.
            TXT,
        'tech' => <<<'TXT'
            Sector: tech company / startup / digital agency. Audiences: business decision makers, technical teams, candidates.
            Must show: concrete problems solved, services/products, how you work, pricing or how to get a quote, case studies, team and culture, careers, contacts / demo request.
            Tone: confident, concrete, benefit-oriented, no buzzword soup.
            Never invent: client names or logos, certifications, uptime figures, prices, case-study results.
            TXT,
        'commerce' => <<<'TXT'
            Sector: shop / retail / SME. Audiences: local customers, professional buyers.
            Must show: what is sold (categories), why buy here, current offers, ordering and delivery (WhatsApp, phone), payment methods, opening hours, location, customer reviews, FAQ.
            Tone: friendly, trustworthy, practical; simple calls to action.
            Never invent: prices, brands carried, delivery areas or fees, warranties, opening hours.
            TXT,
        'art-deco' => <<<'TXT'
            Sector: art, decoration, design, craftsmanship, photography. Audiences: private clients, businesses, event organisers.
            Must show: style and philosophy, services, portfolio/collections, process from brief to delivery, pricing or quote request, artisans, contacts and appointment.
            Tone: elegant, sensory, inspiring, precise about materials and craft.
            Never invent: past clients, exhibitions, awards, prices, materials not mentioned by the owner.
            TXT,
        'restaurant' => <<<'TXT'
            Sector: restaurant / hotel / guesthouse / caterer. Audiences: local diners, travellers, event organisers.
            Must show: cuisine and signature dishes, menu with prices (or placeholders), rooms and amenities if any, events and private hire, booking process, opening hours, location, reviews.
            Tone: warm, appetising, hospitable.
            Never invent: dishes or prices not given, star ratings, awards, amenities (pool, wifi…) not confirmed by the owner.
            TXT,
        'cabinet' => <<<'TXT'
            Sector: professional services firm (law, accounting, consulting, notary, insurance). Audiences: business owners, individuals, institutions.
            Must show: practice areas, approach, team and qualifications, fee principles, sample engagements (anonymised), resources, contacts and appointments; confidentiality.
            Tone: authoritative, precise, reassuring; never promise outcomes.
            Never invent: bar or registration numbers, client names, case outcomes, qualifications, fees.
            TXT,
        'autre' => <<<'TXT'
            Sector: other organisation or business. Adapt the template's pages to the owner's activity: what they do, for whom, how it works, why trust them, how to contact them.
            TXT,
    ];

    public static function for(?string $sector): string
    {
        return self::COMMON."\n\n".(self::SECTORS[$sector] ?? self::SECTORS['autre']);
    }
}
