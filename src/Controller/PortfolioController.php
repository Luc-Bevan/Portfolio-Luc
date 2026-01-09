<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class PortfolioController extends AbstractController
{
    /**
     * Render a template with translation support.
     * French is default; can override via ?locale=fr|en
     */
    private function renderTranslated(
        Request $request,
        TranslatorInterface $translator,
        string $template,
        string $translationKey,
        array $extra = []
    ): Response {
        // Determine locale: query param overrides default
        $locale = $request->query->get('locale') ?: $this->getParameter('kernel.default_locale');

        // Translate the page title
        $pageTitle = $translator->trans($translationKey, [], 'messages', $locale);

        // Pass locale to Twig for other translatable strings
        return $this->render($template, array_merge([
            'page_title' => $pageTitle,
            'locale' => $locale,
        ], $extra));
    }

    #[Route('/', name: 'home')]
    public function home(Request $request, TranslatorInterface $translator): Response
    {
        $locale = $request->query->get('locale') ?: $this->getParameter('kernel.default_locale');

        return $this->renderTranslated(
            $request,
            $translator,
            'portfolio/home.html.twig',
            'home.title',
            [
                'name' => 'Luc Bevan',
                'tp_group' => 'TP1',
                'intro' => $translator->trans('home.intro', [], 'messages', $locale),
                // optional: pass all home card translations
                'cards' => [
                    'education_title' => $translator->trans('home.cards.education.title', [], 'messages', $locale),
                    'education_text'  => $translator->trans('home.cards.education.text', [], 'messages', $locale),
                    'skills_title'    => $translator->trans('home.cards.skills.title', [], 'messages', $locale),
                    'skills_text'     => $translator->trans('home.cards.skills.text', [], 'messages', $locale),
                    'projects_title'  => $translator->trans('home.cards.projects.title', [], 'messages', $locale),
                    'projects_text'   => $translator->trans('home.cards.projects.text', [], 'messages', $locale),
                ],
            ]
        );
    }

    #[Route('/portfolio', name: 'portfolio')]
    public function portfolio(Request $request, TranslatorInterface $translator): Response
    {
        $locale = $request->query->get('locale') ?: $this->getParameter('kernel.default_locale');

        // Load structured portfolio data if available
        $projectDir = $this->getParameter('kernel.project_dir');
        $yamlPath = $projectDir . '/config/portfolio.yaml';
        $portfolioData = [];
        if (file_exists($yamlPath)) {
            try {
                $portfolioData = Yaml::parseFile($yamlPath) ?: [];
            } catch (\Throwable $e) {
                // silently ignore parse errors and fallback to translations
                $portfolioData = [];
            }
        }

        // Build projects list (merge YAML config with translations)
        $projects = [];
        if (!empty($portfolioData) && isset($portfolioData['projects']) && is_array($portfolioData['projects'])) {
            foreach ($portfolioData['projects'] as $idx => $proj) {
                $id = $proj['id'] ?? null;
                // Choose a single image per project based on its position: first project uses image1, second uses image2, third uses image3.
                $pos = $idx + 1;
                $key = 'image' . $pos;
                $chosen = $proj[$key] ?? ($proj['image'] ?? null);

                // --- MODIFICATION ICI ---
                // On prépare le chemin de l'image pour Twig
                $imagePath = null;
                if ($chosen) {
                    // 1. On retire '/public/' si tu l'as mis dans le YAML (erreur fréquente)
                    // 2. On retire le '/' au début pour avoir un chemin relatif propre
                    $imagePath = ltrim(str_replace('/public/', '', $chosen), '/');
                    
                    // Note : J'ai supprimé le bloc "file_exists" strict.
                    // Si l'image n'existe pas, le navigateur affichera une icône brisée,
                    // ce qui te permettra de faire Clic-Droit > Inspecter pour voir l'erreur.
                }
                // ------------------------

                if ($id) {
                    $projects[] = [
                        'id' => $id,
                        'image' => $imagePath,
                        'title' => $translator->trans('portfolio.' . $id . '.title', [], 'messages', $locale),
                        'desc' => $translator->trans('portfolio.' . $id . '.desc', [], 'messages', $locale),
                    ];
                }
            }
        }

        return $this->renderTranslated($request, $translator, 'portfolio/portfolio.html.twig', 'portfolio.title', [
            'description' => $translator->trans('portfolio.description', [], 'messages', $locale),
            'portfolio_data' => $portfolioData,
            'projects' => $projects,
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(Request $request, TranslatorInterface $translator): Response
    {
        $locale = $request->query->get('locale') ?: $this->getParameter('kernel.default_locale');

        return $this->renderTranslated($request, $translator, 'portfolio/about.html.twig', 'about.title', [
            'intro' => $translator->trans('about.intro', [], 'messages', $locale),
            'personal_title' => $translator->trans('about.personal_title', [], 'messages', $locale),
            'personal_text'  => $translator->trans('about.personal_text', [], 'messages', $locale),
            'projects_title' => $translator->trans('about.projects_title', [], 'messages', $locale),
            'projects' => [
                'project1' => [
                    'title' => $translator->trans('about.cards.project1.title', [], 'messages', $locale),
                    'text'  => $translator->trans('about.cards.project1.text', [], 'messages', $locale),
                ],
                'project2' => [
                    'title' => $translator->trans('about.cards.project2.title', [], 'messages', $locale),
                    'text'  => $translator->trans('about.cards.project2.text', [], 'messages', $locale),
                ],
                'project3' => [
                    'title' => $translator->trans('about.cards.project3.title', [], 'messages', $locale),
                    'text'  => $translator->trans('about.cards.project3.text', [], 'messages', $locale),
                ],
            ],
            'hobbies_title' => $translator->trans('about.hobbies_title', [], 'messages', $locale),
            'hobbies' => [
                'hobby1' => $translator->trans('about.hobbies.hobby1', [], 'messages', $locale),
                'hobby2' => $translator->trans('about.hobbies.hobby2', [], 'messages', $locale),
                'hobby3' => $translator->trans('about.hobbies.hobby3', [], 'messages', $locale),
            ],
            'back_home' => $translator->trans('about.back_home', [], 'messages', $locale),
        ]);
    }

    #[Route('/cv', name: 'cv')]
    public function cv(Request $request, TranslatorInterface $translator): Response
    {
        $locale = $request->query->get('locale') ?: $this->getParameter('kernel.default_locale');

        return $this->renderTranslated($request, $translator, 'portfolio/cv.html.twig', 'cv.title', [
            'header' => $translator->trans('cv.header', [], 'messages', $locale),
            'sections' => [
                'education' => [
                    'title'   => $translator->trans('cv.sections.education.title', [], 'messages', $locale),
                    'content' => $translator->trans('cv.sections.education.content', [], 'messages', $locale),
                ],
                'experience' => [
                    'title'   => $translator->trans('cv.sections.experience.title', [], 'messages', $locale),
                    'content' => $translator->trans('cv.sections.experience.content', [], 'messages', $locale),
                ],
                'skills' => [
                    'title'   => $translator->trans('cv.sections.skills.title', [], 'messages', $locale),
                    'content' => $translator->trans('cv.sections.skills.content', [], 'messages', $locale),
                ],
            ],
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, TranslatorInterface $translator): Response
    {
        $locale = $request->query->get('locale') ?: $this->getParameter('kernel.default_locale');

        return $this->renderTranslated($request, $translator, 'portfolio/contact.html.twig', 'contact.title', [
            'description' => $translator->trans('contact.description', [], 'messages', $locale),
        ]);
    }
}