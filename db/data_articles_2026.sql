-- Données réelles - Guerre en Iran 2026
-- 5 catégories + 10 articles

-- Catégories
INSERT INTO categories (name, slug, description, seo_title, seo_description, status)
VALUES
    ('Actualités Militaires', 'actualites-militaires', 'Suivi des opérations et mouvements militaires en Iran', 'Actualités militaires Iran 2026', 'Dernières informations sur les opérations militaires et stratégies de guerre en Iran.', 'active'),
    ('Politique & Diplomatie', 'politique-diplomatie', 'Négociations diplomatiques et décisions politiques', 'Politique et diplomatie Iran 2026', 'Suivi des négociations diplomatiques et des développements politiques du conflit iranien.', 'active'),
    ('Analyse Géopolitique', 'analyse-geopolitique', 'Analyse des rapports de force régionaux et internationaux', 'Géopolitique Iran 2026', 'Analyses approfondies des impacts géopolitiques et des équilibres régionaux.', 'active'),
    ('Économie & Sanctions', 'economie-sanctions', 'Impact économique des sanctions et du conflit', 'Économie et sanctions Iran 2026', 'Impact économique des sanctions internationales et de la guerre en Iran.', 'active'),
    ('Humanitaire & Civils', 'humanitaire-civils', 'Situation humanitaire et impact sur les populations', 'Situation humanitaire Iran 2026', 'Suivi de la crise humanitaire et l\'impact du conflit sur les populations civiles.', 'active')
ON DUPLICATE KEY UPDATE status = VALUES(status);

-- Articles
INSERT INTO articles (category_id, author_id, title, slug, excerpt, content, image, image_alt, meta_title, meta_description, status, published_at)
VALUES
    (
        1, 1,
        'Iran déploie nouveaux systèmes de défense aérienne avancés',
        'iran-nouveaux-systemes-defense-aerienne',
        'L\'Iran a annoncé le déploiement de systèmes de défense aérienne de nouvelle génération pour renforcer sa couverture défensive face aux menaces aériennes croissantes.',
        '<h2>Renforcement des défenses aériennes</h2><p>Les forces iraniennes ont déployé des systèmes de défense aérienne avancés dans plusieurs régions clés du pays. Ces nouveaux systèmes incluent des radars de dernière génération et des batteries de missiles courte et moyenne portée.</p><h3>Capacités techniques</h3><p>Les nouveaux systèmes permettent une meilleure détection et une réaction plus rapide aux menaces aériennes. Ils intègrent l\'intelligence artificielle pour améliorer le ciblage et réduire les temps de réaction.</p><h3>Déploiement stratégique</h3><p>Les systèmes ont été positionnés autour des installations critiques, des ports pétroliers et des centres urbains majeurs. Cette stratégie vise à créer une bulle de protection contre les attaques aériennes coordonnées.</p>',
        'https://images.unsplash.com/photo-1518611505868-48510c2c5bf3?w=800&h=600&fit=crop', 'Système de défense aérienne iranien', 'Nouveaux systèmes de défense aérienne Iran', 'Iran déploie des systèmes de défense aérienne de nouvelle génération pour renforcer sa sécurité.', 'published', '2026-03-28 09:15:00'
    ),
    (
        2, 1,
        'Pourparlers de paix: vers une résolution du conflit?',
        'pourparlers-paix-resolution-conflit',
        'Les médiateurs régionaux et internationaux ont relancé les négociations diplomatiques avec un nouvel optimisme quant à une possible résolution du conflit iranien.',
        '<h2>Nouvelle dynamique diplomatique</h2><p>Les pourparlers se déroulent à Muscat, avec la participation de représentants de haut niveau des parties en conflit. Les médiateurs des Émirats arabes unis et du Qatar jouent un rôle central.</p><h3>Points clés des négociations</h3><ul><li>Cessation immédiate des hostilités</li><li>Levée progressive des sanctions économiques</li><li>Reconstruction des infrastructures endommagées</li><li>Garanties de sécurité internationales</li></ul><h3>Perspectives d\'avenir</h3><p>Les analystes estiment que cette nouvelle dynamique pourrait conduire à un accord-cadre dans les trois mois suivants, marquant un tournant majeur dans la crise régionale.</p>',
        'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&h=600&fit=crop', 'Table de négociations diplomatiques', 'Pourparlers de paix Iran 2026', 'Dynamique nouvelle dans les négociations diplomatiques pour résoudre le conflit iranien.', 'published', '2026-03-27 14:30:00'
    ),
    (
        3, 1,
        'Alignements régionaux: le rôle des puissances voisines',
        'alignements-regionaux-puissances-voisines',
        'Les pays du Golfe et les acteurs régionaux redéfinissent leurs positions face à l\'évolution du conflit iranien et ses implications pour la stabilité régionale.',
        '<h2>Repositionnement géopolitique</h2><p>La crise iranienne a provoqué un réalignement significatif des puissances régionales. L\'Arabie Saoudite, les Émirats et la Turquie ajustent leurs stratégies en fonction de l\'évolution du conflit.</p><h3>Enjeux énergétiques</h3><p>Le détroit d\'Ormuz reste la clé stratégique. Les perturbations du transit pétrolier ont des répercussions mondiales sur les prix de l\'énergie. Le rôle des puissances régionales est déterminant dans la stabilisation de ces flux.</p><h3>Sécurité collective</h3><p>Un nouvel ordre régional basé sur la coopération économique et la sécurité collective est envisagé. Les discussions portent sur un pacte de non-agression et une architecture de sécurité partagée.</p>',
        'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=800&h=600&fit=crop', 'Carte du Golfe Persique et routes commerciales', 'Alignements géopolitiques régionaux Iran', 'Analyse des repositionnements géopolitiques des puissances régionales face au conflit iranien.', 'published', '2026-03-26 11:45:00'
    ),
    (
        4, 1,
        'Les sanctions contre l\'Iran s\'aggravent: impact économique record',
        'sanctions-iran-impact-economique-record',
        'Les nouvelles sanctions économiques internationales contre l\'Iran ont atteint un niveau sans précédent, affectant gravement tous les secteurs de l\'économie iranienne.',
        '<h2>Ampleur des sanctions</h2><p>Les sanctions couvrent maintenant le secteur pétrolier, bancaire, technologique et les biens de consommation. L\'Iran perd plus de 3 milliards de dollars par jour en revenus pétroliers.</p><h3>Impact sectoriel</h3><ul><li>Secteur pétrolier: perte de 90% des exportations</li><li>Secteur bancaire: gel des actifs internationaux</li><li>Technologie: embargo sur les puces et composants</li><li>Agriculture: restrictions sur les engrais et équipements</li></ul><h3>Conséquences humanitaires</h3><p>L\'inflation atteint 180% annuels. Le taux de chômage grimpe à 25%. Les classes moyennes sont les plus affectées par l\'effondrement de la monnaie locale.</p><h3>Contremesures économiques</h3><p>L\'Iran renforce ses partenariats commerciaux avec la Chine, la Russie et l\'Inde. Le commerce bilatéral se développe en contournant les sanctions occidentales.</p>',
        'https://images.unsplash.com/photo-1460925895917-aaf4b51bae00?w=800&h=600&fit=crop', 'Graphique montrant la chute économique de l\'Iran', 'Sanctions Iran 2026', 'Impact des sanctions économiques internationales sur l\'économie iranienne en 2026.', 'published', '2026-03-25 16:20:00'
    ),
    (
        5, 1,
        'Crise humanitaire: 4 millions de déplacés internes',
        'crise-humanitaire-4-millions-deplaces',
        'La situation humanitaire s\'aggrave drastiquement avec plus de 4 millions de personnes déplacées à l\'intérieur de l\'Iran, selon les organisations internationales.',
        '<h2>Ampleur de la crise de déplacement</h2><p>Les combats ont forcé des millions de civils à quitter leurs maisons. Les camps de réfugiés internes débordent de ressources insuffisantes et d\'accès médical limité.</p><h3>Besoins humanitaires urgents</h3><ul><li>Nourriture et eau potable pour 4 millions de personnes</li><li>Abris d\'urgence dans les conditions climatiques extrêmes</li><li>Services médicaux et vaccinations pour les enfants</li><li>Éducation pour 1,2 million d\'enfants déscolarisés</li></ul><h3>Santé publique</h3><p>Les risques épidémiques augmentent. Le choléra et la dysenterie se propagent dans les camps. Les taux de malnutrition infantile atteignent 35% dans les zones les plus affectées.</p><h3>Accès humanitaire</h3><p>Les organisations d\'aide humanitaire ont un accès limité aux zones de conflit. Les négociations pour des corridors humanitaires restent bloquées malgré les appels des Nations Unies.</p>',
        'https://images.unsplash.com/photo-1559027615-cd2628902d4a?w=800&h=600&fit=crop', 'Camp de réfugiés internes en Iran', 'Crise humanitaire Iran 2026', 'Situation humanitaire critique en Iran avec millions de déplacés et besoins urgents.', 'published', '2026-03-24 13:00:00'
    ),
    (
        1, 1,
        'Offensive iranienne dans le nord: stratégie de pincement',
        'offensive-iranienne-nord-strategie-pincement',
        'L\'Iran lance une nouvelle offensive militaire coordonnée dans les zones frontalières du nord, utilisant une stratégie de mouvement en tenaille pour améliorer ses positions tactiques.',
        '<h2>Détails de l\'offensive</h2><p>L\'offensive implique le déploiement de 150 000 soldats supplémentaires en première ligne. Les opérations combinent attaques terrestres, drones et bombardements d\'artillerie.</p><h3>Objectifs stratégiques</h3><p>Les objectifs incluent le contrôle des hauts plateaux, le sevrage des lignes de ravitaillement adverses et l\'établissement de nouvelles zones tampons défensives.</p><h3>Innovation tactique</h3><p>La nouvelle stratégie intègre l\'utilisation massive de drones pour le renseignement et les attaques ciblées. L\'aviation iranienne soutient les opérations terrestres avec des frappes de précision.</p>',
        'https://images.unsplash.com/photo-1578701222915-dccacc1b8ace?w=800&h=600&fit=crop', 'Soldats iraniens en position offensive', 'Offensive militaire Iran 2026', 'Nouvelle offensive iranienne dans le nord avec stratégie coordonnée.', 'published', '2026-03-23 10:30:00'
    ),
    (
        2, 1,
        'L\'ONU appelle à une enquête internationale sur les crimes de guerre',
        'onu-enquete-crimes-guerre',
        'Le Conseil de sécurité de l\'ONU a voté une résolution mandatant une enquête internationale sur les allégations de crimes de guerre commis par toutes les parties du conflit.',
        '<h2>Mandat d\'enquête international</h2><p>Une commission indépendante a reçu le mandat d\'enquêter sur les violations du droit international humanitaire, les crimes de guerre et les crimes contre l\'humanité.</p><h3>Portée de l\'investigation</h3><ul><li>Bombardements de zones civiles et d\'hôpitaux</li><li>Violations des droits des prisonniers de guerre</li><li>Utilisation d\'armes illégales</li><li>Déplacements forcés de populations</li></ul><h3>Processus et calendrier</h3><p>L\'enquête prendra 18 mois. Les preuves seront rassemblées par des inspecteurs internationaux. Un rapport final sera remis au tribunal pénal international.</p>',
        'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&h=600&fit=crop', 'Bâtiment du siège de l\'ONU à New York', 'Enquête ONU crimes de guerre', 'ONU mandate une enquête internationale sur les allégations de crimes de guerre en Iran.', 'published', '2026-03-22 15:45:00'
    ),
    (
        3, 1,
        'Chine et Russie intensifient leur soutien géopolitique à l\'Iran',
        'chine-russie-soutien-geopolitique-iran',
        'Pékin et Moscou renforcent leur engagement politique et économique envers l\'Iran, redéfinissant les alliances dans la région face aux pressions occidentales.',
        '<h2>Partenariat sino-russe en Iran</h2><p>La Chine et la Russie coordonnent leurs efforts pour soutenir l\'Iran diplomatiquement, économiquement et techniquement. Cette alliance s\'inscrit dans une confrontation plus large entre puissances.</p><h3>Domaines de coopération</h3><ul><li>Commerce d\'énergie et accord pétrolier à long terme</li><li>Transfert technologique militaire et civil</li><li>Investissements en infrastructure</li><li>Soutien diplomatique aux forums internationaux</li></ul><h3>Implications géopolitiques</h3><p>Ce renforcement des liens sino-russes avec l\'Iran crée une nouvelle dynamique multipolaire au Moyen-Orient. Les occidentaux perdent de l\'influence tandis que l\'axe eurasiatique se consolide.</p>',
        'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&h=600&fit=crop', 'Drapeaux de la Chine, Russie et Iran', 'Alliances géopolitiques 2026', 'Chine et Russie intensifient leur soutien géopolitique à l\'Iran.', 'published', '2026-03-21 12:15:00'
    ),
    (
        4, 1,
        'Inflation et crise monétaire: le rial s\'effondre',
        'inflation-crise-monetaire-rial-effondre',
        'La monnaie iranienne s\'effondre face aux sanctions, causant une inflation galopante qui paralyse l\'économie et appauvrit les citoyens ordinaires.',
        '<h2>Chute spectaculaire de la monnaie</h2><p>Le rial iranien a perdu 75% de sa valeur au cours des 12 derniers mois. Le marché noir pour les devises étrangères prospère avec des taux de change officiels complètement déconnectés de la réalité.</p><h3>Inflation record</h3><p>Les prix ont triplé en un an. Un litre de lait coûte maintenant l\'équivalent d\'un salaire quotidien pour les travailleurs ordinaires. L\'alimentation consomme 60% du budget des ménages.</p><h3>Effets sociaux</h3><p>Les classes moyennes s\'appauvrisent. Le secteur public, payé en monnaie faible, fait face à des grèves massives. Les travailleurs ne peuvent plus payer le loyer ni l\'électricité.</p><h3>Contremesures gouvernementales</h3><p>Le gouvernement iranien contrôle les prix artificiellement et limite les retraits bancaires. Ces mesures ne font que pousser davantage vers l\'économie souterraine.</p>',
        'https://images.unsplash.com/photo-1551454014-3fb3c2a2fe5d?w=800&h=600&fit=crop', 'Pièces et billets de monnaie iranienne', 'Crise monétaire Iran 2026', 'Effondrement du rial iranien et inflation record due aux sanctions.', 'published', '2026-03-20 09:30:00'
    ),
    (
        5, 1,
        'Accès aux médicaments: crise sanitaire majeure dans les hôpitaux',
        'acces-medicaments-crise-sanitaire-hopitaux',
        'Les hôpitaux iraniens manquent cruellement de médicaments essentiels, de vaccins et d\'équipements médicaux en raison des sanctions qui bloquent les importations.',
        '<h2>Pénurie médicale critique</h2><p>Les pharmacies font face à des ruptures de stock chroniques. Les médicaments contre le cancer, le diabète et l\'hypertension ne sont plus disponibles. Les patients se tournent vers les marchés noirs à des prix exorbitants.</p><h3>Impact sur la santé publique</h3><ul><li>Augmentation de la mortalité infantile</li><li>Dépistage retardé des maladies graves</li><li>Chirurgies reportées ou annulées</li><li>Pandémies potentielles non contenues</li></ul><h3>Équipements médicaux</h3><p>Les hôpitaux fonctionnent avec des appareils IRM, scanners et respirateurs obsolètes. Les pièces de rechange ne sont pas disponibles. Les étudiants en médecine apprennent sur des équipements datés de 20 ans.</p><h3>Appels humanitaires</h3><p>Les organisations de santé internationales appellent à des exemptions humanitaires pour les médicaments. Quelques pays permettent le transit de fournitures médicales mais les quantités restent insuffisantes.</p>',
        'https://images.unsplash.com/photo-1631217248555-e06ae77e75bc?w=800&h=600&fit=crop', 'Pharmacien iranien face à des rayons vides', 'Crise sanitaire Iran 2026', 'Crise médicale grave en Iran due au manque de médicaments et équipements.', 'published', '2026-03-19 14:45:00'
    );
