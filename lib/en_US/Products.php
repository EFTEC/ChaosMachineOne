<?php
class Products {
	public static $fruit=["Açaí",
                          "Akee",
                          "Apple",
                          "Apricot",
                          "Avocado",
                          "Banana",
                          "Bilberry",
                          "Blackberry",
                          "Blackcurrant",
                          "Black sapote",
                          "Blueberry",
                          "Boysenberry",
                          "Buddha's hand",
                          "Cactus pear",
                          "Crab apple",
                          "Currant",
                          "Cherry",
                          "Cherimoya",
                          "Chico fruit",
                          "Cloudberry",
                          "Coconut",
                          "Cranberry",
                          "Damson",
                          "Date",
                          "Dragonfruit",
                          "Durian",
                          "Elderberry",
                          "Feijoa",
                          "Fig",
                          "Goji berry",
                          "Gooseberry",
                          "Grape",
                          "Raisin",
                          "Grapefruit",
                          "Guava",
                          "Honeyberry",
                          "Huckleberry",
                          "Jabuticaba",
                          "Jackfruit",
                          "Jambul",
                          "Japanese plum",
                          "Jostaberry",
                          "Jujube",
                          "Juniper berry",
                          "Kiwano",
                          "Kiwifruit",
                          "Kumquat",
                          "Lemon",
                          "Lime",
                          "Loganberry",
                          "Loquat",
                          "Longan",
                          "Lychee",
                          "Mango",
                          "Mangosteen",
                          "Marionberry",
                          "Melon",
                          "Cantaloupe",
                          "Honeydew",
                          "Watermelon",
                          "Miracle fruit",
                          "Mulberry",
                          "Nectarine",
                          "Nance",
                          "Orange",
                          "Blood orange",
                          "Clementine",
                          "Mandarine",
                          "Tangerine",
                          "Papaya",
                          "Passionfruit",
                          "Peach",
                          "Pear",
                          "Persimmon",
                          "Plantain",
                          "Plum",
                          "Prune",
                          "Pineapple",
                          "Pineberry",
                          "Plumcot",
                          "Pomegranate",
                          "Pomelo",
                          "Purple mangosteen",
                          "Quince",
                          "Raspberry",
                          "Salmonberry",
                          "Rambutan",
                          "Redcurrant",
                          "Salal berry",
                          "Salak",
                          "Satsuma",
                          "Soursop",
                          "Star apple",
                          "Star fruit",
                          "Strawberry",
                          "Surinam cherry",
                          "Tamarillo",
                          "Tamarind",
                          "Tangelo",
                          "Tayberry",
                          "Ugli fruit",
                          "White currant",
                          "White sapote",
                          "Yuzu"
    ];
	public static $softDrink=['Ambasa',
		'Ameyal',
		'Appletiser',
		'Aquarius',
		'Barqs',
		'Beat',
		'Beverly',
		'Coca-Cola',
		'Caffeine Free Coca-Cola',
		'Coca-Cola Black Cherry Vanilla',
		'Coca-Cola BlāK',
		'Coca-Cola C2',
		'Coca-Cola Clear',
		'Coca-Cola Cherry',
		'Coca-Cola Citra',
		'Coca-Cola Life',
		'Coca-Cola Light',
		'Coca-Cola Light Sango',
		'Coca-Cola Orange',
		'Coca-Cola Raspberry',
		'Coca-Cola Vanilla',
		'Coca-Cola with Lemon',
		'Coca-Cola with Lime',
		'Coca-Cola Zero',
		'Diet Coke',
		'Diet Coke with Lemon',
		'Diet Coke Lime',
		'Diet Coke Plus',
		'Diet Coke with Citrus Zest',
		'New Coke',
		'Dasani',
		'Delaware Punch',
		'Fanta',
		'Fanta Citrus',
		'Fanta Exotic',
		'Fantasy',
		'Cream Soda',
		'Grape',
		'Strawberry',
		'Tangerine',
		'Wild Strawberry',
		'Orange',
		'Wild Cherry',
		'Fresca',
		'Frescolihi',
		'Frescolita',
		'Full Throttle',
		'Inca Kola',
		'Diet Inca Kola',
		'Leed',
		'Lift',
		'Lilt',
		'Limca',
		'Mello Yello',
		'Mr. Pibb',
		'Pibb Xtra',
		'Sprite',
		'Sprite Cranberry',
		'Sprite Ice',
		'Sprite Remix',
		'Sprite Zero',
		'Surge',
		'Tab',
		'Tab Clear',
		'Tab Energy',
		'Thums Up',
		'Vault',
		'Vault Red Blitz',
		'AMP Energy',
		'Dukes',
		'Fayrouz',
		'Mirinda',
		'Mountain Dew',
		'Caffeine Free Mountain Dew',
		'Diet Mountain Dew',
		'KickStart',
		'Mountain Dew Baja Blast',
		'Mountain Dew Code Red',
		'Mountain Dew Game Fuel',
		'Mountain Dew Live Wire',
		'MDX',
		'Mountain Dew Pitch Black',
		'Mountain Dew Pitch Black II',
		'Mountain Dew Revolution',
		'Mountain Dew Sangrita',
		'Mountain Dew Super Nova',
		'Mountain Dew Voltage',
		'Mountain Dew White Out',
		'Mountain Dew ICE Lemon Lime',
		'Mug Root Beer',
		'Natures Twist',
		'Pepsi',
		'Diet Pepsi',
		'Pepsi Cola',
		'Pepsi Jazz Strawberries & Cream',
		'Pepsi Jazz Black Cherry & Vanilla',
		'Pepsi Lime',
		'Pepsi Max',
		'Pepsi Perfect',
		'Crystal Pepsi',
		'Pepsi Fire',
		'Sierra Mist',
		'50/50',
		'7 Up',
		'A&W Cream Soda',
		'A&W Root Beer',
		'Barrelhead Root Beer',
		'Cactus Cooler',
		'Canada Dry',
		'Canfields Diet Chocolate Fudge',
		'Crush',
		'Dr Pepper',
		'Floats',
		'Gini',
		'Hawaiian Punch',
		'Hires Root Beer',
		'IBC Root Beer',
		'Orangina',
		'RC Cola',
		'Diet Rite',
		'Nehi',
		'Ricqlès',
		'Schweppes',
		'Squirt',
		'Stewarts Fountain Classics',
		'Sun Drop',
		'Sunkist',
		'Sussex Golden',
		'Venom Energy',
		'Vernors',
		'Wink',
		'Cola',
		'Dr. Thunder',
		'Fruit Punch',
		'Ginger Ale',
		'Grapette',
		'Grapefruit',
		'Lemonade',
		'Mountain Lightning',
		'Orangette',
		'Pineapple',
		'Raspberry',
		'Red Tornado',
		'Root Beer',
		'Twist-Up',
		'Hamoud',
		'Selecto',
		'Slim ( Lemon )',
		'Slim ( Orange )',
		'orange blaze',
		'Faygo',
		'La Croix Sparkling Water',
		'Shasta'];
}