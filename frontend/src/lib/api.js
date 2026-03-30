const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

const mockArticles = [
  {
    id: 1,
    slug: "contexte-regional",
    title: "Contexte regional",
    excerpt: "Une vue d'ensemble des acteurs et des dynamiques du conflit.",
    content:
      "Cette page presente un article detaille sur le contexte regional et les implications diplomatiques.",
    categorySlug: "geopolitique",
    categoryName: "Geopolitique",
    imageUrl: "https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1200&q=80&auto=format&fit=crop",
    imageAlt: "Montagnes et horizon pour illustrer un contexte geopolitique",
    publishedAt: "2026-03-15"
  },
  {
    id: 2,
    slug: "impact-civil",
    title: "Impact civil",
    excerpt: "Etat des lieux sur les repercussions humanitaires.",
    content:
      "Analyse des consequences civiles et des besoins prioritaires des populations touchees.",
    categorySlug: "societe",
    categoryName: "Societe",
    imageUrl: "https://images.unsplash.com/photo-1470163395405-d2b80e7450ed?w=1200&q=80&auto=format&fit=crop",
    imageAlt: "Ville au coucher du soleil",
    publishedAt: "2026-03-17"
  },
  {
    id: 3,
    slug: "analyses-economiques",
    title: "Analyses economiques",
    excerpt: "Les effets macroeconomiques observes sur la region.",
    content:
      "Etude des fluctuations commerciales, budgetaires et energetiques dans le contexte actuel.",
    categorySlug: "economie",
    categoryName: "Economie",
    imageUrl: "https://images.unsplash.com/photo-1465146344425-f00d5f5c8f07?w=1200&q=80&auto=format&fit=crop",
    imageAlt: "Graphiques et chiffres economiques",
    publishedAt: "2026-03-19"
  }
];

const mockCategories = [
  {
    slug: "geopolitique",
    name: "Geopolitique",
    description: "Analyses diplomatiques, militaires et strategiques."
  },
  {
    slug: "societe",
    name: "Societe",
    description: "Repercussions sociales, civiles et humanitaires."
  },
  {
    slug: "economie",
    name: "Economie",
    description: "Donnees macro, marches et politiques budgetaires."
  }
];

const mockUsers = [
  { id: 1, name: "Admin Principal", email: "admin@example.com", role: "admin" },
  { id: 2, name: "Redacteur", email: "redac@example.com", role: "editor" }
];

async function fetchFromApi(path) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      Accept: "application/json"
    },
    cache: "no-store"
  });

  if (!response.ok) {
    throw new Error(`API request failed for ${path} (${response.status})`);
  }

  return response.json();
}

export async function getArticles() {
  try {
    const data = await fetchFromApi("/articles");
    return Array.isArray(data) ? data : mockArticles;
  } catch {
    return mockArticles;
  }
}

export async function getArticleBySlug(slug) {
  try {
    const data = await fetchFromApi(`/articles/${slug}`);
    return data || null;
  } catch {
    return mockArticles.find((article) => article.slug === slug) || null;
  }
}

export async function getCategories() {
  try {
    const data = await fetchFromApi("/categories");
    return Array.isArray(data) ? data : mockCategories;
  } catch {
    return mockCategories;
  }
}

export async function getCategoryBySlug(slug) {
  try {
    const data = await fetchFromApi(`/categories/${slug}`);
    const category = data || null;
    if (!category) {
      return null;
    }

    const relatedArticles = (await getArticles()).filter(
      (article) => article.categorySlug === slug
    );

    return {
      ...category,
      articles: relatedArticles
    };
  } catch {
    const category = mockCategories.find((item) => item.slug === slug) || null;
    if (!category) {
      return null;
    }

    return {
      ...category,
      articles: mockArticles.filter((article) => article.categorySlug === slug)
    };
  }
}

export async function getUsers() {
  try {
    const data = await fetchFromApi("/users");
    return Array.isArray(data) ? data : mockUsers;
  } catch {
    return mockUsers;
  }
}