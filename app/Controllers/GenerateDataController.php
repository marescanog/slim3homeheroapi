<?php


namespace App\Controllers;

use App\Models\User;
use App\Models\Homeowner;
use App\Models\GenerateData;
use App\Models\File;
use App\Models\Worker;
use App\Models\Support;
use App\Models\SupportTicket;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class GenerateDataController
{
    protected  $user;

    protected  $homeowner;

    protected  $file;

    protected  $worker;

    protected  $customResponse;

    protected $supportAgent;

    protected $supportTicket;

    protected  $validator;

    protected  $generateData;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->supportAgent = new Support();

        $this->supportTicket = new GenerateData();

        $this->file = new File();

        $this->worker = new Worker();

        $this->validator = new Validator();

        $this->user = new User();

        $this->homeowner = new Homeowner();

        $this->generateData = new GenerateData();

        $this->array_lname = array(
            'Abbott',
            'Acevedo',
            'Acosta',
            'Adams',
            'Adkins',
            'Aguilar',
            'Aguirre',
            'Albert',
            'Alexander',
            'Alford',
            'Allen',
            'Allison',
            'Alston',
            'Alvarado',
            'Alvarez',
            'Anderson',
            'Andrews',
            'Anthony',
            'Armstrong',
            'Arnold',
            'Ashley',
            'Atkins',
            'Atkinson',
            'Austin',
            'Avery',
            'Avila',
            'Ayala',
            'Ayers',
            'Bailey',
            'Baird',
            'Baker',
            'Baldwin',
            'Ball',
            'Ballard',
            'Banks',
            'Barber',
            'Barker',
            'Barlow',
            'Barnes',
            'Barnett',
            'Barr',
            'Barrera',
            'Barrett',
            'Barron',
            'Barry',
            'Bartlett',
            'Barton',
            'Bass',
            'Bates',
            'Battle',
            'Bauer',
            'Baxter',
            'Beach',
            'Bean',
            'Beard',
            'Beasley',
            'Beck',
            'Becker',
            'Bell',
            'Bender',
            'Benjamin',
            'Bennett',
            'Benson',
            'Bentley',
            'Benton',
            'Berg',
            'Berger',
            'Bernard',
            'Berry',
            'Best',
            'Bird',
            'Bishop',
            'Black',
            'Blackburn',
            'Blackwell',
            'Blair',
            'Blake',
            'Blanchard',
            'Blankenship',
            'Blevins',
            'Bolton',
            'Bond',
            'Bonner',
            'Booker',
            'Boone',
            'Booth',
            'Bowen',
            'Bowers',
            'Bowman',
            'Boyd',
            'Boyer',
            'Boyle',
            'Bradford',
            'Bradley',
            'Bradshaw',
            'Brady',
            'Branch',
            'Bray',
            'Brennan',
            'Brewer',
            'Bridges',
            'Briggs',
            'Bright',
            'Britt',
            'Brock',
            'Brooks',
            'Brown',
            'Browning',
            'Bruce',
            'Bryan',
            'Bryant',
            'Buchanan',
            'Buck',
            'Buckley',
            'Buckner',
            'Bullock',
            'Burch',
            'Burgess',
            'Burke',
            'Burks',
            'Burnett',
            'Burns',
            'Burris',
            'Burt',
            'Burton',
            'Bush',
            'Butler',
            'Byers',
            'Byrd',
            'Cabrera',
            'Cain',
            'Calderon',
            'Caldwell',
            'Calhoun',
            'Callahan',
            'Camacho',
            'Cameron',
            'Campbell',
            'Campos',
            'Cannon',
            'Cantrell',
            'Cantu',
            'Cardenas',
            'Carey',
            'Carlson',
            'Carney',
            'Carpenter',
            'Carr',
            'Carrillo',
            'Carroll',
            'Carson',
            'Carter',
            'Carver',
            'Case',
            'Casey',
            'Cash',
            'Castaneda',
            'Castillo',
            'Castro',
            'Cervantes',
            'Chambers',
            'Chan',
            'Chandler',
            'Chaney',
            'Chang',
            'Chapman',
            'Charles',
            'Chase',
            'Chavez',
            'Chen',
            'Cherry',
            'Christensen',
            'Christian',
            'Church',
            'Clark',
            'Clarke',
            'Clay',
            'Clayton',
            'Clements',
            'Clemons',
            'Cleveland',
            'Cline',
            'Cobb',
            'Cochran',
            'Coffey',
            'Cohen',
            'Cole',
            'Coleman',
            'Collier',
            'Collins',
            'Colon',
            'Combs',
            'Compton',
            'Conley',
            'Conner',
            'Conrad',
            'Contreras',
            'Conway',
            'Cook',
            'Cooke',
            'Cooley',
            'Cooper',
            'Copeland',
            'Cortez',
            'Cote',
            'Cotton',
            'Cox',
            'Craft',
            'Craig',
            'Crane',
            'Crawford',
            'Crosby',
            'Cross',
            'Cruz',
            'Cummings',
            'Cunningham',
            'Curry',
            'Curtis',
            'Dale',
            'Dalton',
            'Daniel',
            'Daniels',
            'Daugherty',
            'Davenport',
            'David',
            'Davidson',
            'Davis',
            'Dawson',
            'Day',
            'Dean',
            'Decker',
            'Dejesus',
            'Delacruz',
            'Delaney',
            'Deleon',
            'Delgado',
            'Dennis',
            'Diaz',
            'Dickerson',
            'Dickson',
            'Dillard',
            'Dillon',
            'Dixon',
            'Dodson',
            'Dominguez',
            'Donaldson',
            'Donovan',
            'Dorsey',
            'Dotson',
            'Douglas',
            'Downs',
            'Doyle',
            'Drake',
            'Dudley',
            'Duffy',
            'Duke',
            'Duncan',
            'Dunlap',
            'Dunn',
            'Duran',
            'Durham',
            'Dyer',
            'Eaton',
            'Edwards',
            'Elliott',
            'Ellis',
            'Ellison',
            'Emerson',
            'England',
            'English',
            'Erickson',
            'Espinoza',
            'Estes',
            'Estrada',
            'Evans',
            'Everett',
            'Ewing',
            'Farley',
            'Farmer',
            'Farrell',
            'Faulkner',
            'Ferguson',
            'Fernandez',
            'Ferrell',
            'Fields',
            'Figueroa',
            'Finch',
            'Finley',
            'Fischer',
            'Fisher',
            'Fitzgerald',
            'Fitzpatrick',
            'Fleming',
            'Fletcher',
            'Flores',
            'Flowers',
            'Floyd',
            'Flynn',
            'Foley',
            'Forbes',
            'Ford',
            'Foreman',
            'Foster',
            'Fowler',
            'Fox',
            'Francis',
            'Franco',
            'Frank',
            'Franklin',
            'Franks',
            'Frazier',
            'Frederick',
            'Freeman',
            'French',
            'Frost',
            'Fry',
            'Frye',
            'Fuentes',
            'Fuller',
            'Fulton',
            'Gaines',
            'Gallagher',
            'Gallegos',
            'Galloway',
            'Gamble',
            'Garcia',
            'Gardner',
            'Garner',
            'Garrett',
            'Garrison',
            'Garza',
            'Gates',
            'Gay',
            'Gentry',
            'George',
            'Gibbs',
            'Gibson',
            'Gilbert',
            'Giles',
            'Gill',
            'Gillespie',
            'Gilliam',
            'Gilmore',
            'Glass',
            'Glenn',
            'Glover',
            'Goff',
            'Golden',
            'Gomez',
            'Gonzales',
            'Gonzalez',
            'Good',
            'Goodman',
            'Goodwin',
            'Gordon',
            'Gould',
            'Graham',
            'Grant',
            'Graves',
            'Gray',
            'Green',
            'Greene',
            'Greer',
            'Gregory',
            'Griffin',
            'Griffith',
            'Grimes',
            'Gross',
            'Guerra',
            'Guerrero',
            'Guthrie',
            'Gutierrez',
            'Guy',
            'Guzman',
            'Hahn',
            'Hale',
            'Haley',
            'Hall',
            'Hamilton',
            'Hammond',
            'Hampton',
            'Hancock',
            'Haney',
            'Hansen',
            'Hanson',
            'Hardin',
            'Harding',
            'Hardy',
            'Harmon',
            'Harper',
            'Harrell',
            'Harrington',
            'Harris',
            'Harrison',
            'Hart',
            'Hartman',
            'Harvey',
            'Hatfield',
            'Hawkins',
            'Hayden',
            'Hayes',
            'Haynes',
            'Hays',
            'Head',
            'Heath',
            'Hebert',
            'Henderson',
            'Hendricks',
            'Hendrix',
            'Henry',
            'Hensley',
            'Henson',
            'Herman',
            'Hernandez',
            'Herrera',
            'Herring',
            'Hess',
            'Hester',
            'Hewitt',
            'Hickman',
            'Hicks',
            'Higgins',
            'Hill',
            'Hines',
            'Hinton',
            'Hobbs',
            'Hodge',
            'Hodges',
            'Hoffman',
            'Hogan',
            'Holcomb',
            'Holden',
            'Holder',
            'Holland',
            'Holloway',
            'Holman',
            'Holmes',
            'Holt',
            'Hood',
            'Hooper',
            'Hoover',
            'Hopkins',
            'Hopper',
            'Horn',
            'Horne',
            'Horton',
            'House',
            'Houston',
            'Howard',
            'Howe',
            'Howell',
            'Hubbard',
            'Huber',
            'Hudson',
            'Huff',
            'Huffman',
            'Hughes',
            'Hull',
            'Humphrey',
            'Hunt',
            'Hunter',
            'Hurley',
            'Hurst',
            'Hutchinson',
            'Hyde',
            'Ingram',
            'Irwin',
            'Jackson',
            'Jacobs',
            'Jacobson',
            'James',
            'Jarvis',
            'Jefferson',
            'Jenkins',
            'Jennings',
            'Jensen',
            'Jimenez',
            'Johns',
            'Johnson',
            'Johnston',
            'Jones',
            'Jordan',
            'Joseph',
            'Joyce',
            'Joyner',
            'Juarez',
            'Justice',
            'Kane',
            'Kaufman',
            'Keith',
            'Keller',
            'Kelley',
            'Kelly',
            'Kemp',
            'Kennedy',
            'Kent',
            'Kerr',
            'Key',
            'Kidd',
            'Kim',
            'King',
            'Kinney',
            'Kirby',
            'Kirk',
            'Kirkland',
            'Klein',
            'Kline',
            'Knapp',
            'Knight',
            'Knowles',
            'Knox',
            'Koch',
            'Kramer',
            'Lamb',
            'Lambert',
            'Lancaster',
            'Landry',
            'Lane',
            'Lang',
            'Langley',
            'Lara',
            'Larsen',
            'Larson',
            'Lawrence',
            'Lawson',
            'Le',
            'Leach',
            'Leblanc',
            'Lee',
            'Leon',
            'Leonard',
            'Lester',
            'Levine',
            'Levy',
            'Lewis',
            'Lindsay',
            'Lindsey',
            'Little',
            'Livingston',
            'Lloyd',
            'Logan',
            'Long',
            'Lopez',
            'Lott',
            'Love',
            'Lowe',
            'Lowery',
            'Lucas',
            'Luna',
            'Lynch',
            'Lynn',
            'Lyons',
            'Macdonald',
            'Macias',
            'Mack',
            'Madden',
            'Maddox',
            'Maldonado',
            'Malone',
            'Mann',
            'Manning',
            'Marks',
            'Marquez',
            'Marsh',
            'Marshall',
            'Martin',
            'Martinez',
            'Mason',
            'Massey',
            'Mathews',
            'Mathis',
            'Matthews',
            'Maxwell',
            'May',
            'Mayer',
            'Maynard',
            'Mayo',
            'Mays',
            'Mcbride',
            'Mccall',
            'Mccarthy',
            'Mccarty',
            'Mcclain',
            'Mcclure',
            'Mcconnell',
            'Mccormick',
            'Mccoy',
            'Mccray',
            'Mccullough',
            'Mcdaniel',
            'Mcdonald',
            'Mcdowell',
            'Mcfadden',
            'Mcfarland',
            'Mcgee',
            'Mcgowan',
            'Mcguire',
            'Mcintosh',
            'Mcintyre',
            'Mckay',
            'Mckee',
            'Mckenzie',
            'Mckinney',
            'Mcknight',
            'Mclaughlin',
            'Mclean',
            'Mcleod',
            'Mcmahon',
            'Mcmillan',
            'Mcneil',
            'Mcpherson',
            'Meadows',
            'Medina',
            'Mejia',
            'Melendez',
            'Melton',
            'Mendez',
            'Mendoza',
            'Mercado',
            'Mercer',
            'Merrill',
            'Merritt',
            'Meyer',
            'Meyers',
            'Michael',
            'Middleton',
            'Miles',
            'Miller',
            'Mills',
            'Miranda',
            'Mitchell',
            'Molina',
            'Monroe',
            'Montgomery',
            'Montoya',
            'Moody',
            'Moon',
            'Mooney',
            'Moore',
            'Morales',
            'Moran',
            'Moreno',
            'Morgan',
            'Morin',
            'Morris',
            'Morrison',
            'Morrow',
            'Morse',
            'Morton',
            'Moses',
            'Mosley',
            'Moss',
            'Mueller',
            'Mullen',
            'Mullins',
            'Munoz',
            'Murphy',
            'Murray',
            'Myers',
            'Nash',
            'Navarro',
            'Neal',
            'Nelson',
            'Newman',
            'Newton',
            'Nguyen',
            'Nichols',
            'Nicholson',
            'Nielsen',
            'Nieves',
            'Nixon',
            'Noble',
            'Noel',
            'Nolan',
            'Norman',
            'Norris',
            'Norton',
            'Nunez',
            'Obrien',
            'Ochoa',
            'Oconnor',
            'Odom',
            'Odonnell',
            'Oliver',
            'Olsen',
            'Olson',
            'Oneal',
            'Oneil',
            'Oneill',
            'Orr',
            'Ortega',
            'Ortiz',
            'Osborn',
            'Osborne',
            'Owen',
            'Owens',
            'Pace',
            'Pacheco',
            'Padilla',
            'Page',
            'Palmer',
            'Park',
            'Parker',
            'Parks',
            'Parrish',
            'Parsons',
            'Pate',
            'Patel',
            'Patrick',
            'Patterson',
            'Patton',
            'Paul',
            'Payne',
            'Pearson',
            'Peck',
            'Pena',
            'Pennington',
            'Perez',
            'Perkins',
            'Perry',
            'Peters',
            'Petersen',
            'Peterson',
            'Petty',
            'Phelps',
            'Phillips',
            'Pickett',
            'Pierce',
            'Pittman',
            'Pitts',
            'Pollard',
            'Poole',
            'Pope',
            'Porter',
            'Potter',
            'Potts',
            'Powell',
            'Powers',
            'Pratt',
            'Preston',
            'Price',
            'Prince',
            'Pruitt',
            'Puckett',
            'Pugh',
            'Quinn',
            'Ramirez',
            'Ramos',
            'Ramsey',
            'Randall',
            'Randolph',
            'Rasmussen',
            'Ratliff',
            'Ray',
            'Raymond',
            'Reed',
            'Reese',
            'Reeves',
            'Reid',
            'Reilly',
            'Reyes',
            'Reynolds',
            'Rhodes',
            'Rice',
            'Rich',
            'Richard',
            'Richards',
            'Richardson',
            'Richmond',
            'Riddle',
            'Riggs',
            'Riley',
            'Rios',
            'Rivas',
            'Rivera',
            'Rivers',
            'Roach',
            'Robbins',
            'Roberson',
            'Roberts',
            'Robertson',
            'Robinson',
            'Robles',
            'Rocha',
            'Rodgers',
            'Rodriguez',
            'Rodriquez',
            'Rogers',
            'Rojas',
            'Rollins',
            'Roman',
            'Romero',
            'Rosa',
            'Rosales',
            'Rosario',
            'Rose',
            'Ross',
            'Roth',
            'Rowe',
            'Rowland',
            'Roy',
            'Ruiz',
            'Rush',
            'Russell',
            'Russo',
            'Rutledge',
            'Ryan',
            'Salas',
            'Salazar',
            'Salinas',
            'Sampson',
            'Sanchez',
            'Sanders',
            'Sandoval',
            'Sanford',
            'Santana',
            'Santiago',
            'Santos',
            'Sargent',
            'Saunders',
            'Savage',
            'Sawyer',
            'Schmidt',
            'Schneider',
            'Schroeder',
            'Schultz',
            'Schwartz',
            'Scott',
            'Sears',
            'Sellers',
            'Serrano',
            'Sexton',
            'Shaffer',
            'Shannon',
            'Sharp',
            'Sharpe',
            'Shaw',
            'Shelton',
            'Shepard',
            'Shepherd',
            'Sheppard',
            'Sherman',
            'Shields',
            'Short',
            'Silva',
            'Simmons',
            'Simon',
            'Simpson',
            'Sims',
            'Singleton',
            'Skinner',
            'Slater',
            'Sloan',
            'Small',
            'Smith',
            'Snider',
            'Snow',
            'Snyder',
            'Solis',
            'Solomon',
            'Sosa',
            'Soto',
            'Sparks',
            'Spears',
            'Spence',
            'Spencer',
            'Stafford',
            'Stanley',
            'Stanton',
            'Stark',
            'Steele',
            'Stein',
            'Stephens',
            'Stephenson',
            'Stevens',
            'Stevenson',
            'Stewart',
            'Stokes',
            'Stone',
            'Stout',
            'Strickland',
            'Strong',
            'Stuart',
            'Suarez',
            'Sullivan',
            'Summers',
            'Sutton',
            'Swanson',
            'Sweeney',
            'Sweet',
            'Sykes',
            'Talley',
            'Tanner',
            'Tate',
            'Taylor',
            'Terrell',
            'Terry',
            'Thomas',
            'Thompson',
            'Thornton',
            'Tillman',
            'Todd',
            'Torres',
            'Townsend',
            'Tran',
            'Travis',
            'Trevino',
            'Trujillo',
            'Tucker',
            'Turner',
            'Tyler',
            'Tyson',
            'Underwood',
            'Valdez',
            'Valencia',
            'Valentine',
            'Valenzuela',
            'Vance',
            'Vang',
            'Vargas',
            'Vasquez',
            'Vaughan',
            'Vaughn',
            'Vazquez',
            'Vega',
            'Velasquez',
            'Velazquez',
            'Velez',
            'Villarreal',
            'Vincent',
            'Vinson',
            'Wade',
            'Wagner',
            'Walker',
            'Wall',
            'Wallace',
            'Waller',
            'Walls',
            'Walsh',
            'Walter',
            'Walters',
            'Walton',
            'Ward',
            'Ware',
            'Warner',
            'Warren',
            'Washington',
            'Waters',
            'Watkins',
            'Watson',
            'Watts',
            'Weaver',
            'Webb',
            'Weber',
            'Webster',
            'Weeks',
            'Weiss',
            'Welch',
            'Wells',
            'West',
            'Wheeler',
            'Whitaker',
            'White',
            'Whitehead',
            'Whitfield',
            'Whitley',
            'Whitney',
            'Wiggins',
            'Wilcox',
            'Wilder',
            'Wiley',
            'Wilkerson',
            'Wilkins',
            'Wilkinson',
            'William',
            'Williams',
            'Williamson',
            'Willis',
            'Wilson',
            'Winters',
            'Wise',
            'Witt',
            'Wolf',
            'Wolfe',
            'Wong',
            'Wood',
            'Woodard',
            'Woods',
            'Woodward',
            'Wooten',
            'Workman',
            'Wright',
            'Wyatt',
            'Wynn',
            'Yang',
            'Yates',
            'York',
            'Young',
            'Zamora',
            'Zimmerman');

        $this->array_fname = array("Aaran", "Aaren", "Aarez", "Aarman", "Aaron", "Aaron-James", "Aarron", "Aaryan", "Aaryn", "Aayan", "Aazaan", "Abaan", "Abbas", "Abdallah", "Abdalroof", "Abdihakim", "Abdirahman", "Abdisalam", "Abdul", "Abdul-Aziz", "Abdulbasir", "Abdulkadir", "Abdulkarem", "Abdulkhader", "Abdullah", "Abdul-Majeed", "Abdulmalik", "Abdul-Rehman", "Abdur", "Abdurraheem", "Abdur-Rahman", "Abdur-Rehmaan", "Abel", "Abhinav", "Abhisumant", "Abid", "Abir", "Abraham", "Abu", "Abubakar", "Ace", "Adain", "Adam", "Adam-James", "Addison", "Addisson", "Adegbola", "Adegbolahan", "Aden", "Adenn", "Adie", "Adil", "Aditya", "Adnan", "Adrian", "Adrien", "Aedan", "Aedin", "Aedyn", "Aeron", "Afonso", "Ahmad", "Ahmed", "Ahmed-Aziz", "Ahoua", "Ahtasham", "Aiadan", "Aidan", "Aiden", "Aiden-Jack", "Aiden-Vee", "Aidian", "Aidy", "Ailin", "Aiman", "Ainsley", "Ainslie", "Airen", "Airidas", "Airlie", "AJ", "Ajay", "A-Jay", "Ajayraj", "Akan", "Akram", "Al", "Ala", "Alan", "Alanas", "Alasdair", "Alastair", "Alber", "Albert", "Albie", "Aldred", "Alec", "Aled", "Aleem", "Aleksandar", "Aleksander", "Aleksandr", "Aleksandrs", "Alekzander", "Alessandro", "Alessio", "Alex", "Alexander", "Alexei", "Alexx", "Alexzander", "Alf", "Alfee", "Alfie", "Alfred", "Alfy", "Alhaji", "Al-Hassan", "Ali", "Aliekber", "Alieu", "Alihaider", "Alisdair", "Alishan", "Alistair", "Alistar", "Alister", "Aliyaan", "Allan", "Allan-Laiton", "Allen", "Allesandro", "Allister", "Ally", "Alphonse", "Altyiab", "Alum", "Alvern", "Alvin", "Alyas", "Amaan", "Aman", "Amani", "Ambanimoh", "Ameer", "Amgad", "Ami", "Amin", "Amir", "Ammaar", "Ammar", "Ammer", "Amolpreet", "Amos", "Amrinder", "Amrit", "Amro", "Anay", "Andrea", "Andreas", "Andrei", "Andrejs", "Andrew", "Andy", "Anees", "Anesu", "Angel", "Angelo", "Angus", "Anir", "Anis", "Anish", "Anmolpreet", "Annan", "Anndra", "Anselm", "Anthony", "Anthony-John", "Antoine", "Anton", "Antoni", "Antonio", "Antony", "Antonyo", "Anubhav", "Aodhan", "Aon", "Aonghus", "Apisai", "Arafat", "Aran", "Arandeep", "Arann", "Aray", "Arayan", "Archibald", "Archie", "Arda", "Ardal", "Ardeshir", "Areeb", "Areez", "Aref", "Arfin", "Argyle", "Argyll", "Ari", "Aria", "Arian", "Arihant", "Aristomenis", "Aristotelis", "Arjuna", "Arlo", "Armaan", "Arman", "Armen", "Arnab", "Arnav", "Arnold", "Aron", "Aronas", "Arran", "Arrham", "Arron", "Arryn", "Arsalan", "Artem", "Arthur", "Artur", "Arturo", "Arun", "Arunas", "Arved", "Arya", "Aryan", "Aryankhan", "Aryian", "Aryn", "Asa", "Asfhan", "Ash", "Ashlee-jay", "Ashley", "Ashton", "Ashton-Lloyd", "Ashtyn", "Ashwin", "Asif", "Asim", "Aslam", "Asrar", "Ata", "Atal", "Atapattu", "Ateeq", "Athol", "Athon", "Athos-Carlos", "Atli", "Atom", "Attila", "Aulay", "Aun", "Austen", "Austin", "Avani", "Averon", "Avi", "Avinash", "Avraham", "Awais", "Awwal", "Axel", "Ayaan", "Ayan", "Aydan", "Ayden", "Aydin", "Aydon", "Ayman", "Ayomide", "Ayren", "Ayrton", "Aytug", "Ayub", "Ayyub", "Azaan", "Azedine", "Azeem", "Azim", "Aziz", "Azlan", "Azzam", "Azzedine", "Babatunmise", "Babur", "Bader", "Badr", "Badsha", "Bailee", "Bailey", "Bailie", "Bailley", "Baillie", "Baley", "Balian", "Banan", "Barath", "Barkley", "Barney", "Baron", "Barrie", "Barry", "Bartlomiej", "Bartosz", "Basher", "Basile", "Baxter", "Baye", "Bayley", "Beau", "Beinn", "Bekim", "Believe", "Ben", "Bendeguz", "Benedict", "Benjamin", "Benjamyn", "Benji", "Benn", "Bennett", "Benny", "Benoit", "Bentley", "Berkay", "Bernard", "Bertie", "Bevin", "Bezalel", "Bhaaldeen", "Bharath", "Bilal", "Bill", "Billy", "Binod", "Bjorn", "Blaike", "Blaine", "Blair", "Blaire", "Blake", "Blazej", "Blazey", "Blessing", "Blue", "Blyth", "Bo", "Boab", "Bob", "Bobby", "Bobby-Lee", "Bodhan", "Boedyn", "Bogdan", "Bohbi", "Bony", "Bowen", "Bowie", "Boyd", "Bracken", "Brad", "Bradan", "Braden", "Bradley", "Bradlie", "Bradly", "Brady", "Bradyn", "Braeden", "Braiden", "Brajan", "Brandan", "Branden", "Brandon", "Brandonlee", "Brandon-Lee", "Brandyn", "Brannan", "Brayden", "Braydon", "Braydyn", "Breandan", "Brehme", "Brendan", "Brendon", "Brendyn", "Breogan", "Bret", "Brett", "Briaddon", "Brian", "Brodi", "Brodie", "Brody", "Brogan", "Broghan", "Brooke", "Brooklin", "Brooklyn", "Bruce", "Bruin", "Bruno", "Brunon", "Bryan", "Bryce", "Bryden", "Brydon", "Brydon-Craig", "Bryn", "Brynmor", "Bryson", "Buddy", "Bully", "Burak", "Burhan", "Butali", "Butchi", "Byron", "Cabhan", "Cadan", "Cade", "Caden", "Cadon", "Cadyn", "Caedan", "Caedyn", "Cael", "Caelan", "Caelen", "Caethan", "Cahl", "Cahlum", "Cai", "Caidan", "Caiden", "Caiden-Paul", "Caidyn", "Caie", "Cailaen", "Cailean", "Caileb-John", "Cailin", "Cain", "Caine", "Cairn", "Cal", "Calan", "Calder", "Cale", "Calean", "Caleb", "Calen", "Caley", "Calib", "Calin", "Callahan", "Callan", "Callan-Adam", "Calley", "Callie", "Callin", "Callum", "Callun", "Callyn", "Calum", "Calum-James", "Calvin", "Cambell", "Camerin", "Cameron", "Campbel", "Campbell", "Camron", "Caolain", "Caolan", "Carl", "Carlo", "Carlos", "Carrich", "Carrick", "Carson", "Carter", "Carwyn", "Casey", "Casper", "Cassy", "Cathal", "Cator", "Cavan", "Cayden", "Cayden-Robert", "Cayden-Tiamo", "Ceejay", "Ceilan", "Ceiran", "Ceirin", "Ceiron", "Cejay", "Celik", "Cephas", "Cesar", "Cesare", "Chad", "Chaitanya", "Chang-Ha", "Charles", "Charley", "Charlie", "Charly", "Chase", "Che", "Chester", "Chevy", "Chi", "Chibudom", "Chidera", "Chimsom", "Chin", "Chintu", "Chiqal", "Chiron", "Chris", "Chris-Daniel", "Chrismedi", "Christian", "Christie", "Christoph", "Christopher", "Christopher-Lee", "Christy", "Chu", "Chukwuemeka", "Cian", "Ciann", "Ciar", "Ciaran", "Ciarian", "Cieran", "Cillian", "Cillin", "Cinar", "CJ", "C-Jay", "Clark", "Clarke", "Clayton", "Clement", "Clifford", "Clyde", "Cobain", "Coban", "Coben", "Cobi", "Cobie", "Coby", "Codey", "Codi", "Codie", "Cody", "Cody-Lee", "Coel", "Cohan", "Cohen", "Colby", "Cole", "Colin", "Coll", "Colm", "Colt", "Colton", "Colum", "Colvin", "Comghan", "Conal", "Conall", "Conan", "Conar", "Conghaile", "Conlan", "Conley", "Conli", "Conlin", "Conlly", "Conlon", "Conlyn", "Connal", "Connall", "Connan", "Connar", "Connel", "Connell", "Conner", "Connolly", "Connor", "Connor-David", "Conor", "Conrad", "Cooper", "Copeland", "Coray", "Corben", "Corbin", "Corey", "Corey-James", "Corey-Jay", "Cori", "Corie", "Corin", "Cormac", "Cormack", "Cormak", "Corran", "Corrie", "Cory", "Cosmo", "Coupar", "Craig", "Craig-James", "Crawford", "Creag", "Crispin", "Cristian", "Crombie", "Cruiz", "Cruz", "Cuillin", "Cullen", "Cullin", "Curtis", "Cyrus", "Daanyaal", "Daegan", "Daegyu", "Dafydd", "Dagon", "Dailey", "Daimhin", "Daithi", "Dakota", "Daksh", "Dale", "Dalong", "Dalton", "Damian", "Damien", "Damon", "Dan", "Danar", "Dane", "Danial", "Daniel", "Daniele", "Daniel-James", "Daniels", "Daniil", "Danish", "Daniyal", "Danniel", "Danny", "Dante", "Danyal", "Danyil", "Danys", "Daood", "Dara", "Darach", "Daragh", "Darcy", "D'arcy", "Dareh", "Daren", "Darien", "Darius", "Darl", "Darn", "Darrach", "Darragh", "Darrel", "Darrell", "Darren", "Darrie", "Darrius", "Darroch", "Darryl", "Darryn", "Darwyn", "Daryl", "Daryn", "Daud", "Daumantas", "Davi", "David", "David-Jay", "David-Lee", "Davie", "Davis", "Davy", "Dawid", "Dawson", "Dawud", "Dayem", "Daymian", "Deacon", "Deagan", "Dean", "Deano", "Decklan", "Declain", "Declan", "Declyan", "Declyn", "Dedeniseoluwa", "Deecan", "Deegan", "Deelan", "Deklain-Jaimes", "Del", "Demetrius", "Denis", "Deniss", "Dennan", "Dennin", "Dennis", "Denny", "Dennys", "Denon", "Denton", "Denver", "Denzel", "Deon", "Derek", "Derick", "Derin", "Dermot", "Derren", "Derrie", "Derrin", "Derron", "Derry", "Derryn", "Deryn", "Deshawn", "Desmond", "Dev", "Devan", "Devin", "Devlin", "Devlyn", "Devon", "Devrin", "Devyn", "Dex", "Dexter", "Dhani", "Dharam", "Dhavid", "Dhyia", "Diarmaid", "Diarmid", "Diarmuid", "Didier", "Diego", "Diesel", "Diesil", "Digby", "Dilan", "Dilano", "Dillan", "Dillon", "Dilraj", "Dimitri", "Dinaras", "Dion", "Dissanayake", "Dmitri", "Doire", "Dolan", "Domanic", "Domenico", "Domhnall", "Dominic", "Dominick", "Dominik", "Donald", "Donnacha", "Donnie", "Dorian", "Dougal", "Douglas", "Dougray", "Drakeo", "Dre", "Dregan", "Drew", "Dugald", "Duncan", "Duriel", "Dustin", "Dylan", "Dylan-Jack", "Dylan-James", "Dylan-John", "Dylan-Patrick", "Dylin", "Dyllan", "Dyllan-James", "Dyllon", "Eadie", "Eagann", "Eamon", "Eamonn", "Eason", "Eassan", "Easton", "Ebow", "Ed", "Eddie", "Eden", "Ediomi", "Edison", "Eduardo", "Eduards", "Edward", "Edwin", "Edwyn", "Eesa", "Efan", "Efe", "Ege", "Ehsan", "Ehsen", "Eiddon", "Eidhan", "Eihli", "Eimantas", "Eisa", "Eli", "Elias", "Elijah", "Eliot", "Elisau", "Eljay", "Eljon", "Elliot", "Elliott", "Ellis", "Ellisandro", "Elshan", "Elvin", "Elyan", "Emanuel", "Emerson", "Emil", "Emile", "Emir", "Emlyn", "Emmanuel", "Emmet", "Eng", "Eniola", "Enis", "Ennis", "Enrico", "Enrique", "Enzo", "Eoghain", "Eoghan", "Eoin", "Eonan", "Erdehan", "Eren", "Erencem", "Eric", "Ericlee", "Erik", "Eriz", "Ernie-Jacks", "Eroni", "Eryk", "Eshan", "Essa", "Esteban", "Ethan", "Etienne", "Etinosa", "Euan", "Eugene", "Evan", "Evann", "Ewan", "Ewen", "Ewing", "Exodi", "Ezekiel", "Ezra", "Fabian", "Fahad", "Faheem", "Faisal", "Faizaan", "Famara", "Fares", "Farhaan", "Farhan", "Farren", "Farzad", "Fauzaan", "Favour", "Fawaz", "Fawkes", "Faysal", "Fearghus", "Feden", "Felix", "Fergal", "Fergie", "Fergus", "Ferre", "Fezaan", "Fiachra", "Fikret", "Filip", "Filippo", "Finan", "Findlay", "Findlay-James", "Findlie", "Finlay", "Finley", "Finn", "Finnan", "Finnean", "Finnen", "Finnlay", "Finnley", "Fintan", "Fionn", "Firaaz", "Fletcher", "Flint", "Florin", "Flyn", "Flynn", "Fodeba", "Folarinwa", "Forbes", "Forgan", "Forrest", "Fox", "Francesco", "Francis", "Francisco", "Franciszek", "Franco", "Frank", "Frankie", "Franklin", "Franko", "Fraser", "Frazer", "Fred", "Freddie", "Frederick", "Fruin", "Fyfe", "Fyn", "Fynlay", "Fynn", "Gabriel", "Gallagher", "Gareth", "Garren", "Garrett", "Garry", "Gary", "Gavin", "Gavin-Lee", "Gene", "Geoff", "Geoffrey", "Geomer", "Geordan", 
"Geordie", "George", "Georgia", "Georgy", "Gerard", "Ghyll", "Giacomo", "Gian", "Giancarlo", "Gianluca", "Gianmarco", "Gideon", "Gil", "Gio", "Girijan", "Girius", "Gjan", "Glascott", "Glen", "Glenn", "Gordon", "Grady", "Graeme", "Graham", "Grahame", "Grant", "Grayson", "Greg", "Gregor", "Gregory", "Greig", "Griffin", "Griffyn", "Grzegorz", "Guang", "Guerin", "Guillaume", "Gurardass", "Gurdeep", "Gursees", "Gurthar", "Gurveer", "Gurwinder", "Gus", "Gustav", "Guthrie", "Guy", "Gytis", "Habeeb", "Hadji", "Hadyn", "Hagun", "Haiden", "Haider", "Hamad", "Hamid", "Hamish", "Hamza", "Hamzah", "Han", "Hansen", "Hao", "Hareem", "Hari", "Harikrishna", "Haris", "Harish", "Harjeevan", "Harjyot", "Harlee", "Harleigh", "Harley", "Harman", "Harnek", "Harold", "Haroon", "Harper", "Harri", "Harrington", "Harris", "Harrison", "Harry", "Harvey", "Harvie", "Harvinder", "Hasan", "Haseeb", "Hashem", "Hashim", "Hassan", "Hassanali", "Hately", "Havila", "Hayden", "Haydn", "Haydon", "Haydyn", "Hcen", "Hector", "Heddle", "Heidar", "Heini", "Hendri", "Henri", "Henry", "Herbert", "Heyden", "Hiro", "Hirvaansh", "Hishaam", "Hogan", "Honey", "Hong", "Hope", "Hopkin", "Hosea", "Howard", "Howie", "Hristomir", "Hubert", "Hugh", "Hugo", "Humza", "Hunter", "Husnain", "Hussain", "Hussan", "Hussnain", "Hussnan", "Hyden", "I", "Iagan", "Iain", "Ian", "Ibraheem", "Ibrahim", "Idahosa", "Idrees", "Idris", "Iestyn", "Ieuan", "Igor", "Ihtisham", "Ijay", "Ikechukwu", "Ikemsinachukwu", "Ilyaas", "Ilyas", "Iman", "Immanuel", "Inan", "Indy", "Ines", "Innes", "Ioannis", "Ireayomide", "Ireoluwa", "Irvin", "Irvine", "Isa", "Isaa", "Isaac", "Isaiah", "Isak", "Isher", "Ishwar", "Isimeli", "Isira", "Ismaeel", "Ismail", "Israel", "Issiaka", "Ivan", "Ivar", "Izaak", "J", "Jaay", "Jac", "Jace", "Jack", "Jacki", "Jackie", "Jack-James", "Jackson", "Jacky", "Jacob", "Jacques", "Jad", "Jaden", "Jadon", "Jadyn", "Jae", "Jagat", "Jago", "Jaheim", "Jahid", "Jahy", "Jai", "Jaida", "Jaiden", "Jaidyn", "Jaii", "Jaime", "Jai-Rajaram", "Jaise", "Jak", "Jake", "Jakey", "Jakob", "Jaksyn", "Jakub", "Jamaal", "Jamal", "Jameel", "Jameil", "James", "James-Paul", "Jamey", "Jamie", "Jan", "Jaosha", "Jardine", "Jared", "Jarell", "Jarl", "Jarno", "Jarred", "Jarvi", "Jasey-Jay", "Jasim", "Jaskaran", "Jason", "Jasper", "Jaxon", "Jaxson", "Jay", "Jaydan", "Jayden", "Jayden-James", "Jayden-Lee", "Jayden-Paul", "Jayden-Thomas", "Jaydn", "Jaydon", "Jaydyn", "Jayhan", "Jay-Jay", "Jayke", "Jaymie", "Jayse", "Jayson", "Jaz", "Jazeb", "Jazib", "Jazz", "Jean", "Jean-Lewis", "Jean-Pierre", "Jebadiah", "Jed", "Jedd", "Jedidiah", "Jeemie", "Jeevan", "Jeffrey", "Jensen", "Jenson", "Jensyn", "Jeremy", "Jerome", "Jeronimo", "Jerrick", "Jerry", "Jesse", "Jesuseun", "Jeswin", "Jevan", "Jeyun", "Jez", "Jia", "Jian", "Jiao", "Jimmy", "Jincheng", "JJ", "Joaquin", "Joash", "Jock", "Jody", "Joe", "Joeddy", "Joel", "Joey", "Joey-Jack", "Johann", "Johannes", "Johansson", "John", "Johnathan", "Johndean", "Johnjay", "John-Michael", "Johnnie", "Johnny", "Johnpaul", "John-Paul", "John-Scott", "Johnson", "Jole", "Jomuel", "Jon", "Jonah", "Jonatan", "Jonathan", "Jonathon", "Jonny", "Jonothan", "Jon-Paul", "Jonson", "Joojo", "Jordan", "Jordi", "Jordon", "Jordy", "Jordyn", "Jorge", "Joris", "Jorryn", "Josan", "Josef", "Joseph", "Josese", "Josh", "Joshiah", "Joshua", "Josiah", "Joss", "Jostelle", "Joynul", "Juan", "Jubin", "Judah", "Jude", "Jules", "Julian", "Julien", "Jun", "Junior", "Jura", "Justan", "Justin", "Justinas", "Kaan", "Kabeer", "Kabir", "Kacey", "Kacper", "Kade", "Kaden", "Kadin", "Kadyn", "Kaeden", "Kael", "Kaelan", "Kaelin", "Kaelum", "Kai", "Kaid", "Kaidan", "Kaiden", "Kaidinn", "Kaidyn", "Kaileb", "Kailin", "Kain", "Kaine", "Kainin", "Kainui", "Kairn", "Kaison", "Kaiwen", "Kajally", "Kajetan", "Kalani", "Kale", "Kaleb", "Kaleem", "Kal-el", "Kalen", "Kalin", "Kallan", "Kallin", "Kalum", "Kalvin", "Kalvyn", "Kameron", "Kames", "Kamil", "Kamran", "Kamron", "Kane", "Karam", "Karamvir", "Karandeep", "Kareem", "Karim", "Karimas", "Karl", "Karol", "Karson", "Karsyn", "Karthikeya", "Kasey", "Kash", "Kashif", "Kasim", "Kasper", "Kasra", "Kavin", "Kayam", "Kaydan", "Kayden", "Kaydin", "Kaydn", "Kaydyn", "Kaydyne", "Kayleb", "Kaylem", "Kaylum", "Kayne", "Kaywan", "Kealan", "Kealon", "Kean", "Keane", "Kearney", "Keatin", "Keaton", "Keavan", "Keayn", "Kedrick", "Keegan", "Keelan", "Keelin", "Keeman", "Keenan", "Keenan-Lee", "Keeton", "Kehinde", "Keigan", "Keilan", "Keir", "Keiran", "Keiren", "Keiron", "Keiryn", "Keison", "Keith", "Keivlin", "Kelam", "Kelan", "Kellan", "Kellen", "Kelso", "Kelum", "Kelvan", "Kelvin", "Ken", "Kenan", "Kendall", "Kendyn", "Kenlin", "Kenneth", "Kensey", "Kenton", "Kenyon", "Kenzeigh", "Kenzi", "Kenzie", "Kenzo", "Kenzy", "Keo", "Ker", "Kern", "Kerr", "Kevan", "Kevin", "Kevyn", "Kez", "Khai", "Khalan", "Khaleel", "Khaya", "Khevien", "Khizar", "Khizer", "Kia", "Kian", "Kian-James", "Kiaran", "Kiarash", "Kie", "Kiefer", "Kiegan", "Kienan", "Kier", "Kieran", "Kieran-Scott", "Kieren", "Kierin", "Kiern", "Kieron", "Kieryn", "Kile", "Killian", "Kimi", "Kingston", "Kinneil", "Kinnon", "Kinsey", "Kiran", "Kirk", "Kirwin", "Kit", "Kiya", "Kiyonari", "Kjae", "Klein", "Klevis", "Kobe", "Kobi", "Koby", "Koddi", "Koden", "Kodi", "Kodie", "Kody", "Kofi", "Kogan", "Kohen", "Kole", "Konan", "Konar", "Konnor", "Konrad", "Koray", "Korben", "Korbyn", "Korey", "Kori", "Korrin", "Kory", "Koushik", "Kris", "Krish", "Krishan", "Kriss", "Kristian", "Kristin", "Kristofer", "Kristoffer", "Kristopher", "Kruz", "Krzysiek", "Krzysztof", "Ksawery", "Ksawier", "Kuba", "Kurt", "Kurtis", "Kurtis-Jae", "Kyaan", "Kyan", "Kyde", "Kyden", "Kye", "Kyel", "Kyhran", "Kyie", "Kylan", "Kylar", "Kyle", "Kyle-Derek", "Kylian", "Kym", "Kynan", "Kyral", "Kyran", "Kyren", "Kyrillos", "Kyro", "Kyron", "Kyrran", "Lachlainn", "Lachlan", "Lachlann", "Lael", "Lagan", "Laird", "Laison", "Lakshya", "Lance", "Lancelot", "Landon", "Lang", "Lasse", "Latif", "Lauchlan", "Lauchlin", "Laughlan", "Lauren", "Laurence", "Laurie", "Lawlyn", "Lawrence", "Lawrie", "Lawson", "Layne", "Layton", "Lee", "Leigh", "Leigham", "Leighton", "Leilan", "Leiten", "Leithen", "Leland", "Lenin", "Lennan", "Lennen", "Lennex", "Lennon", "Lennox", "Lenny", "Leno", "Lenon", "Lenyn", "Leo", "Leon", "Leonard", "Leonardas", "Leonardo", "Lepeng", "Leroy", "Leven", "Levi", "Levon", "Levy", "Lewie", "Lewin", "Lewis", "Lex", "Leydon", "Leyland", "Leylann", "Leyton", "Liall", "Liam", "Liam-Stephen", "Limo", "Lincoln", "Lincoln-John", "Lincon", "Linden", "Linton", "Lionel", "Lisandro", "Litrell", "Liyonela-Elam", "LLeyton", "Lliam", "Lloyd", "Lloyde", "Loche", "Lochlan", "Lochlann", "Lochlan-Oliver", "Lock", "Lockey", "Logan", "Logann", "Logan-Rhys", "Loghan", "Lokesh", "Loki", "Lomond", "Lorcan", "Lorenz", "Lorenzo", "Lorne", "Loudon", "Loui", "Louie", "Louis", "Loukas", "Lovell", "Luc", "Luca", "Lucais", "Lucas", "Lucca", "Lucian", "Luciano", "Lucien", "Lucus", "Luic", "Luis", "Luk", "Luka", "Lukas", "Lukasz", "Luke", "Lukmaan", "Luqman", "Lyall", "Lyle", "Lyndsay", "Lysander", "Maanav", "Maaz", "Mac", "Macallum", "Macaulay", "Macauley", "Macaully", "Machlan", "Maciej", "Mack", "Mackenzie", "Mackenzy", "Mackie", "Macsen", "Macy", "Madaki", "Maddison", "Maddox", "Madison", "Madison-Jake", "Madox", "Mael", "Magnus", "Mahan", "Mahdi", "Mahmoud", "Maias", "Maison", "Maisum", "Maitlind", "Majid", "Makensie", "Makenzie", "Makin", "Maksim", "Maksymilian", "Malachai", "Malachi", "Malachy", "Malakai", "Malakhy", "Malcolm", "Malik", "Malikye", "Malo", "Ma'moon", "Manas", "Maneet", "Manmohan", "Manolo", "Manson", "Mantej", "Manuel", "Manus", "Marc", "Marc-Anthony", "Marcel", "Marcello", "Marcin", "Marco", "Marcos", "Marcous", "Marcquis", "Marcus", "Mario", "Marios", "Marius", "Mark", "Marko", "Markus", "Marley", "Marlin", "Marlon", "Maros", "Marshall", "Martin", "Marty", "Martyn", "Marvellous", "Marvin", "Marwan", "Maryk", "Marzuq", "Mashhood", "Mason", "Mason-Jay", "Masood", "Masson", "Matas", "Matej", "Mateusz", "Mathew", "Mathias", "Mathu", "Mathuyan", "Mati", "Matt", "Matteo", "Matthew", "Matthew-William", "Matthias", "Max", "Maxim", "Maximilian", "Maximillian", "Maximus", "Maxwell", "Maxx", "Mayeul", "Mayson", "Mazin", "Mcbride", "McCaulley", "McKade", "McKauley", "McKay", "McKenzie", "McLay", "Meftah", "Mehmet", "Mehraz", "Meko", "Melville", "Meshach", "Meyzhward", "Micah", "Michael", "Michael-Alexander", "Michael-James", "Michal", "Michat", "Micheal", "Michee", "Mickey", "Miguel", "Mika", "Mikael", "Mikee", "Mikey", "Mikhail", "Mikolaj", "Miles", "Millar", "Miller", "Milo", "Milos", "Milosz", "Mir", "Mirza", "Mitch", "Mitchel", "Mitchell", "Moad", "Moayd", "Mobeen", "Modoulamin", "Modu", "Mohamad", "Mohamed", "Mohammad", "Mohammad-Bilal", "Mohammed", "Mohanad", "Mohd", "Momin", "Momooreoluwa", "Montague", "Montgomery", "Monty", "Moore", "Moosa", "Moray", "Morgan", "Morgyn", "Morris", "Morton", "Moshy", "Motade", "Moyes", "Msughter", "Mueez", "Muhamadjavad", "Muhammad", "Muhammed", "Muhsin", "Muir", "Munachi", "Muneeb", "Mungo", "Munir", "Munmair", "Munro", "Murdo", "Murray", "Murrough", "Murry", "Musa", "Musse", "Mustafa", "Mustapha", "Muzammil", "Muzzammil", "Mykie", "Myles", "Mylo", "Nabeel", "Nadeem", "Nader", "Nagib", "Naif", "Nairn", "Narvic", "Nash", "Nasser", "Nassir", "Natan", "Nate", "Nathan", "Nathanael", "Nathanial", "Nathaniel", "Nathan-Rae", "Nawfal", "Nayan", "Neco", "Neil", "Nelson", "Neo", "Neshawn", "Nevan", "Nevin", "Ngonidzashe", "Nial", "Niall", "Nicholas", "Nick", "Nickhill", "Nicki", "Nickson", "Nicky", "Nico", "Nicodemus", "Nicol", "Nicolae", "Nicolas", "Nidhish", "Nihaal", "Nihal", "Nikash", "Nikhil", "Niki", "Nikita", "Nikodem", "Nikolai", "Nikos", "Nilav", "Niraj", "Niro", "Niven", "Noah", "Noel", "Nolan", "Noor", "Norman", "Norrie", "Nuada", "Nyah", "Oakley", "Oban", "Obieluem", "Obosa", "Odhran", "Odin", "Odynn", "Ogheneochuko", "Ogheneruno", "Ohran", "Oilibhear", "Oisin", "Ojima-Ojo", "Okeoghene", "Olaf", "Ola-Oluwa", "Olaoluwapolorimi", "Ole", "Olie", 
"Oliver", "Olivier", "Oliwier", "Ollie", "Olurotimi", "Oluwadamilare", "Oluwadamiloju", "Oluwafemi", "Oluwafikunayomi", "Oluwalayomi", "Oluwatobiloba", "Oluwatoni", "Omar", "Omri", "Oran", "Orin", "Orlando", "Orley", "Orran", "Orrick", "Orrin", "Orson", "Oryn", "Oscar", "Osesenagha", "Oskar", "Ossian", "Oswald", "Otto", "Owain", "Owais", "Owen", "Owyn", "Oz", "Ozzy", "Pablo", "Pacey", "Padraig", "Paolo", "Pardeepraj", "Parkash", "Parker", "Pascoe", "Pasquale", "Patrick", "Patrick-John", "Patrikas", "Patryk", "Paul", "Pavit", "Pawel", "Pawlo", "Pearce", "Pearse", "Pearsen", "Pedram", "Pedro", "Peirce", "Peiyan", "Pele", "Peni", "Peregrine", "Peter", "Phani", "Philip", "Philippos", "Phinehas", "Phoenix", "Phoevos", "Pierce", "Pierre-Antoine", "Pieter", "Pietro", "Piotr", "Porter", "Prabhjoit", "Prabodhan", "Praise", "Pranav", "Pravin", "Precious", "Prentice", "Presley", "Preston", "Preston-Jay", "Prinay", "Prince", "Prithvi", "Promise", "Puneetpaul", "Pushkar", "Qasim", "Qirui", "Quinlan", "Quinn", "Radmiras", "Raees", "Raegan", "Rafael", "Rafal", "Rafferty", "Rafi", "Raheem", "Rahil", "Rahim", "Rahman", "Raith", "Raithin", "Raja", "Rajab-Ali", "Rajan", "Ralfs", "Ralph", "Ramanas", "Ramit", "Ramone", "Ramsay", "Ramsey", "Rana", "Ranolph", "Raphael", "Rasmus", "Rasul", "Raul", "Raunaq", "Ravin", "Ray", "Rayaan", "Rayan", "Rayane", "Rayden", "Rayhan", "Raymond", "Rayne", "Rayyan", "Raza", "Reace", "Reagan", "Reean", "Reece", "Reed", "Reegan", "Rees", "Reese", "Reeve", "Regan", "Regean", "Reggie", "Rehaan", "Rehan", "Reice", "Reid", "Reigan", "Reilly", "Reily", "Reis", "Reiss", "Remigiusz", "Remo", "Remy", "Ren", "Renars", "Reng", "Rennie", "Reno", "Reo", "Reuben", "Rexford", "Reynold", "Rhein", "Rheo", "Rhett", "Rheyden", "Rhian", "Rhoan", "Rholmark", "Rhoridh", "Rhuairidh", "Rhuan", "Rhuaridh", "Rhudi", "Rhy", "Rhyan", "Rhyley", "Rhyon", "Rhys", "Rhys-Bernard", "Rhyse", "Riach", "Rian", "Ricards", "Riccardo", "Ricco", "Rice", "Richard", "Richey", "Richie", "Ricky", "Rico", "Ridley", "Ridwan", "Rihab", "Rihan", "Rihards", "Rihonn", "Rikki", "Riley", "Rio", "Rioden", "Rishi", "Ritchie", "Rivan", "Riyadh", "Riyaj", "Roan", "Roark", "Roary", "Rob", "Robbi", "Robbie", "Robbie-lee", "Robby", "Robert", "Robert-Gordon", "Robertjohn", "Robi", "Robin", "Rocco", "Roddy", "Roderick", "Rodrigo", "Roen", "Rogan", "Roger", "Rohaan", "Rohan", "Rohin", "Rohit", "Rokas", "Roman", "Ronald", "Ronan", "Ronan-Benedict", "Ronin", "Ronnie", "Rooke", "Roray", "Rori", "Rorie", "Rory", "Roshan", "Ross", "Ross-Andrew", "Rossi", "Rowan", "Rowen", "Roy", "Ruadhan", "Ruaidhri", "Ruairi", "Ruairidh", "Ruan", "Ruaraidh", "Ruari", "Ruaridh", "Ruben", "Rubhan", "Rubin", "Rubyn", "Rudi", "Rudy", "Rufus", "Rui", "Ruo", "Rupert", "Ruslan", "Russel", "Russell", "Ryaan", "Ryan", "Ryan-Lee", "Ryden", "Ryder", "Ryese", "Ryhs", "Rylan", "Rylay", "Rylee", "Ryleigh", "Ryley", "Rylie", "Ryo", "Ryszard", "Saad", "Sabeen", "Sachkirat", "Saffi", "Saghun", "Sahaib", "Sahbian", "Sahil", "Saif", "Saifaddine", "Saim", "Sajid", "Sajjad", "Salahudin", "Salman", "Salter", "Salvador", "Sam", "Saman", "Samar", "Samarjit", "Samatar", "Sambrid", "Sameer", "Sami", "Samir", "Sami-Ullah", "Samual", "Samuel", "Samuela", "Samy", "Sanaullah", "Sandro", "Sandy", "Sanfur", "Sanjay", "Santiago", "Santino", "Satveer", "Saul", "Saunders", "Savin", "Sayad", "Sayeed", "Sayf", "Scot", "Scott", "Scott-Alexander", "Seaan", "Seamas", "Seamus", "Sean", "Seane", "Sean-James", "Sean-Paul", "Sean-Ray", "Seb", "Sebastian", "Sebastien", "Selasi", "Seonaidh", "Sephiroth", "Sergei", "Sergio", "Seth", "Sethu", "Seumas", "Shaarvin", "Shadow", "Shae", "Shahmir", "Shai", "Shane", "Shannon", "Sharland", "Sharoz", "Shaughn", "Shaun", "Shaunpaul", "Shaun-Paul", "Shaun-Thomas", "Shaurya", "Shaw", "Shawn", "Shawnpaul", "Shay", "Shayaan", "Shayan", "Shaye", "Shayne", "Shazil", "Shea", "Sheafan", "Sheigh", "Shenuk", "Sher", "Shergo", "Sheriff", "Sherwyn", "Shiloh", "Shiraz", "Shreeram", "Shreyas", "Shyam", "Siddhant", "Siddharth", "Sidharth", "Sidney", "Siergiej", "Silas", "Simon", "Sinai", "Skye", "Sofian", "Sohaib", "Sohail", "Soham", "Sohan", "Sol", "Solomon", "Sonneey", "Sonni", "Sonny", "Sorley", "Soul", "Spencer", "Spondon", "Stanislaw", "Stanley", "Stefan", "Stefano", "Stefin", "Stephen", "Stephenjunior", "Steve", "Steven", "Steven-lee", "Stevie", "Stewart", "Stewarty", "Strachan", "Struan", "Stuart", "Su", "Subhaan", "Sudais", "Suheyb", "Suilven", "Sukhi", "Sukhpal", "Sukhvir", "Sulayman", "Sullivan", "Sultan", "Sung", "Sunny", "Suraj", "Surien", "Sweyn", "Syed", "Sylvain", "Symon", "Szymon", "Tadd", "Taddy", "Tadhg", "Taegan", "Taegen", "Tai", "Tait", "Taiwo", "Talha", "Taliesin", "Talon", "Talorcan", "Tamar", "Tamiem", "Tammam", "Tanay", "Tane", "Tanner", "Tanvir", "Tanzeel", "Taonga", "Tarik", "Tariq-Jay", "Tate", "Taylan", "Taylar", "Tayler", "Taylor", "Taylor-Jay", "Taylor-Lee", "Tayo", "Tayyab", "Tayye", "Tayyib", "Teagan", "Tee", "Teejay", "Tee-jay", "Tegan", "Teighen", "Teiyib", "Te-Jay", "Temba", "Teo", "Teodor", "Teos", "Terry", "Teydren", "Theo", "Theodore", "Thiago", "Thierry", "Thom", "Thomas", "Thomas-Jay", "Thomson", "Thorben", "Thorfinn", "Thrinei", "Thumbiko", "Tiago", "Tian", "Tiarnan", "Tibet", "Tieran", "Tiernan", "Timothy", "Timucin", "Tiree", "Tisloh", "Titi", "Titus", "Tiylar", "TJ", "Tjay", "T-Jay", "Tobey", "Tobi", "Tobias", "Tobie", "Toby", "Todd", "Tokinaga", "Toluwalase", "Tom", "Tomas", "Tomasz", "Tommi-Lee", "Tommy", "Tomson", "Tony", "Torin", "Torquil", "Torran", "Torrin", "Torsten", "Trafford", "Trai", "Travis", "Tre", "Trent", "Trey", "Tristain", "Tristan", "Troy", "Tubagus", "Turki", "Turner", "Ty", "Ty-Alexander", "Tye", "Tyelor", "Tylar", "Tyler", "Tyler-James", "Tyler-Jay", "Tyllor", "Tylor", "Tymom", "Tymon", "Tymoteusz", "Tyra", "Tyree", "Tyrnan", "Tyrone", "Tyson", "Ubaid", "Ubayd", "Uchenna", "Uilleam", "Umair", "Umar", "Umer", "Umut", "Urban", "Uri", "Usman", "Uzair", "Uzayr", "Valen", "Valentin", "Valentino", "Valery", "Valo", "Vasyl", "Vedantsinh", "Veeran", "Victor", "Victory", "Vinay", "Vince", "Vincent", "Vincenzo", "Vinh", "Vinnie", "Vithujan", "Vladimir", "Vladislav", "Vrishin", "Vuyolwethu", "Wabuya", "Wai", "Walid", "Wallace", "Walter", "Waqaas", "Warkhas", "Warren", "Warrick", "Wasif", "Wayde", "Wayne", "Wei", "Wen", "Wesley", "Wesley-Scott", "Wiktor", "Wilkie", "Will", "William", "William-John", "Willum", "Wilson", "Windsor", "Wojciech", "Woyenbrakemi", "Wyatt", "Wylie", "Wynn", "Xabier", "Xander", "Xavier", "Xiao", "Xida", "Xin", "Xue", "Yadgor", "Yago", "Yahya", "Yakup", "Yang", "Yanick", "Yann", "Yannick", "Yaseen", "Yasin", "Yasir", "Yassin", "Yoji", "Yong", "Yoolgeun", "Yorgos", "Youcef", "Yousif", "Youssef", "Yu", "Yuanyu", "Yuri", "Yusef", "Yusuf", "Yves", "Zaaine", "Zaak", "Zac", "Zach", "Zachariah", "Zacharias", "Zacharie", "Zacharius", "Zachariya", "Zachary", "Zachary-Marc", "Zachery", "Zack", "Zackary", "Zaid", "Zain", "Zaine", "Zaineddine", "Zainedin", "Zak", "Zakaria", "Zakariya", "Zakary", "Zaki", "Zakir", "Zakk", "Zamaar", "Zander", "Zane", "Zarran", "Zayd", "Zayn", "Zayne", "Ze", "Zechariah", "Zeek", "Zeeshan", "Zeid", "Zein", "Zen", "Zendel", "Zenith", "Zennon", "Zeph", "Zerah", "Zhen", "Zhi", "Zhong", "Zhuo", "Zi", "Zidane", "Zijie", "Zinedine", "Zion", "Zishan", "Ziya", "Ziyaan", "Zohaib", "Zohair", "Zoubaeir", "Zubair", "Zubayr", "Zuriel");

        $this->total_fname = count($this->array_fname)-1;
        $this->total_lname = count($this->array_lname)-1;

        $this->array_streetname = array("M. de Santos Street", "Soriano Avenue", "F. A. Reyes Street", "Raja Matanda Street", "De los Santos Street", "Legarda Street", "Florentino Torres Street", "Alfonso Mendoza Street", "Felix Huertas Road", "Juan Luna Street", "	Engracia Cruz-Reyes Street", "Teodora Alonzo Street", "Antonio Villegas Street", "José Laurel Street", "Recto Avenue", "Padre Burgos Avenue", "Gerardo Tuazon Street", "Varona Street", "Bautista Street", "A. Lorenzo Jr. Street", "Bilibid Viejo Street", "Victorino Mapa Street", "J. Figueras Street", "Manrique Street", "Escoda Street", "Herrera Street", "President Quirino Avenue Extension", "Madre Ignacia Street", "Lardizabal Street", "Tolentino Street", "San Juan de Letrán Street", "A. C. Delgado Street", "Felipe Agoncillo Street", "Natividad Almeda-López Street", "Norberto Ty Street", "R. Cristóbal Street", "F. Torres Street", "Adriatico Street", "Angel Linao Street", "Burke Street", "Maestranza Street", "Urdaneta Street", "Quezon Boulevard", "Valderrama Street", "Legaspi Street", "Victoria Street", "San Antón Street", "Muralla Street", "Cabildo Street (NW of Soriano Avenue)", "Numancia Street", "Trinidad Street", "J. Nolasco Street", "San Marcelino Street", "Gen. Luna Street", "Salcepuedes Street", "Quezon Boulevard", "Urbiztondo Street", "Roxas Bridge", "Benavidez Street (N of Recto Avenue)", "Salas Street", "Calle Divisoria", "Calle Díaz", "Calle de Vives", "Calle de Santa Rosa", "Calle El Dorado", "Calle de Santa Mónica", "Calle de San Casiano", "Calle de Quesada", "Calle de Pelota", "Calle del Rondín", "Calle Fundición", "Calle de la Paisana", "Calle de la Escuela", "Calle de la Bomba", "Calle de Echagüe", "Calle de Concepcíon", "Calle Martín Ocampo", "Quezon Avenue", "Mulawen Boulevard", "Calle de Barberos", "Calle de Almacenes", "Dart East", "Calle Dart", "Rizal Avenue", "Carlos Palanca Sr. Street", "Asunción Street", "Vicente Cruz Street", "M. Natividad Street", "Espeleta Street", "Magallanes Street", "María Y. Orosa Street", "Carmen Planas Street", "Solano Street", "Sabino Padilla Street", "Arquiza Street", "Gen. Gerónimo Street", "Dalupan Street", "San Vicente Street", "Luis Ma. Guerrero Street", "Sta. Teresita Street", "Calle Guipit", "Calle Georgia", "Calle Gavey", "Calle Gastambide", "Calle de Lacorzana", "Calle Gardenia", "Calle Gallera", "Pasaje Nozagaray", "Calle Gándara", "Calle San Agustín", "Calle Fonda", "Calle Acuña", "Calle Folgueras", "Calle San Antonio", "Calle Florida", "Calle Farol", "Calle Fapeleta", "Calle Evangelista", "Calle Economía", "Calle Encarnación", "Pedro Gil Street", "Pilar Hidalgo Lim Street", "Dr. M. L. Carreón Street", "United Nations Avenue", "F. Cayco Street", "F. T. Benítez Street", "S. H. Loyola Street", "	M. F. Jhocson Street", "Mahatma Gandhi Street", "Quirino Avenue", "Bambang Street", "G. Masangkay Street", "Bonifacio Drive", "Aguilar Street", "P. Guevarra Street", "Abad Santos Avenue", "Guerrero Street", "Calle Herrán", "Calle Real", "Carretera de Santa Ana", "Calle Real de Paco", "Calle Indiana", "Calle Inviernes", "Calle Isaac Peral", "Calle Cortafuegos", "Calle Isabel", "Calle Kansas (Avenue)", "Calle Juan de Juanes", "Calle Lepanto", "Calle Lipa", "Carmen Street", "Calle Looban", "Calle Luengo", "Calle San José", "Calle Magdalena", "Calle Malecón", "Paseo de Santa Lucía", "Paseo de María Cristina", "Calle Manicninc", "Calle Mangahan", "Calle Manúguit", "Guerrero Street", "D. Romuáldez, Sr. Street", "San Rafael Street", "Doroteo José Street", "Z. P. de Guzmán Street", "J. Quintos, Sr. Street", "Tomás Mapúa Street", "Tayuman Street", "Nicanor Reyes Street", "Jorge Bocobo Street", "Kusang Loób Street", "P. Paterno Street", "Nicanor Padilla Street", "General Luna Street", "E. T. Yuchengco Street", "A. Mabini Street", "Calle Marina", "Calle Marqués de Comillas", "Calle Marquez", "Calle Melba", "Calle Mendoza", "Calle Militar", "Calle Misericordia", "Pasaje Obando", "Calle Morga", "Calle Morayta", "Calle Nebraska", "Calle Negros", "Calle Noria", "Calle Novaliches", "Calzada de Paco", "Calle Nozaleda", "Calle Nueva", "Calle de Bagur", "A. Mabini Street", "Severino Reyes Street", "Padre Faura Street", "G. Apacible Street", "V. Tytana Street", "María Paz Mendoza Guazon Street", "León Guinto Street", "J. Marzán Street", "Santo Cristo Street", "Pitóng Gatang Street", "Del Pan Street", "P. Noval Street", "Juan Nolasco Street", "Sales Street", "Gonzalo Puyat Street", "Del Pilar Street", "General Luna Street", "Calle O'Donell", "Calle Observatorio", "Calle Oregon", "Calle Oriente", "Calle Otis", "Calle Pennsylvania", "Calle Pepín", "Calle Pescadores", "Calle Sagunto", "Calle Pinpin", "Calle Príncipe", "Calle Quezon", "Calle Quiricada", "Calle Quiotan", "Calle Alcala", "Calle Centeno", "Calle Raón", "Calle Real", "Calle Real del Palacio", "Real Street", "Anda Street","Sinagoga Street","E. Remigio Street","Reina Regente Street","Quintín Paredes Street","Ongpin Street","Elcano Street","Arlegui Street","Tomás Pinpin Street","Alhambra Street","San Gregorio Street","Legazpi Street","Kalaw Avenue","Evangelista Street","P. Gomez Street","R. Hidalgo Street","N. Zamora Street","Calle Real del Parián","Calle Recogidas","Calle Remedios","Calle Requesens","Calle Reyna Cristina","Calle Rosario","Calle Lacoste","Calle Enrile","Calle Sacristía","Calle Salinas","Calle San Jerónimo","Calle de Melisa","Calle San Jacinto","Calle San Rafael","Calle San José","Calle San José","Calle San Juan de Dios","Calle San Luis","Calle San Pedro","Calle San Roque","Calle del Gral. Crespo","Calle San Sebastián","Calle Sande","Blumentritt Road","Lopez Jaena Street","F. Jhocson","Camba Street","Dasmariñas Street","J. Nepomuceno Street","Francis P. Yuseco Street","Tetuan Street","Gen. M. Malvar Street","M. Dela Fuente Street","Bambang Street","Dr. Concepción C. Aguila Street","F. M. Gernale Street","Julio Nakpil Street","Pablo Ocampo Street","Novales Street","A. Maceda Street","Calle Sangleyes","Calle Santiago","Calle Sobriedad","Calle Soledad","Calle Soledad","Calle Olivares","Calle Turco","Calle Tanduay","Calle Romero Aquino","Calle Tayabas","Calle Tebuan","Calle Tennessee","Calle Trabajo","Calle Trozo","Calle Tuberías","Calle Unión","Calle Vermont","Calle Vito Cruz (Street)","Calle Vivas","Calle Washington (Street)","Valeriano Fugoso Street","Singalong Street","San Andrés Street","Tejeron Street","Taft Avenue","Roxas Boulevard","Lacson Avenue","Quirino Avenue","South Road","Magistrado Araullo Street","Parade Avenue","Honorio López Boulevard","	Old Panaderos Street","Magsaysay Boulevard","Old Santa Mesa Road","Market Road","Calle Zurbarán","Calzada de Pasay","Calzada de Singalong","Carretera de San Pedro Macati","Columbia Avenue","Dewey Boulevard","Luneta Road", "Governor Forbes Street", "Sampaguita Street","Harrison Boulevard","Joven Boulevard","Katipunan Street","New Luneta Street","North Bay Boulevard","Panaderos Street","Santa Mesa Boulevard","Santa Mesa Road","Venus Street","Macario Asistio, Sr. Avenue","L. P. Leviste Street","Carlos Palanca Street","J. Escrivà Drive","Gil Fernando Avenue","P. Bernardo Street","F. Antonio","Monte de Piedad Street","Alfonso XIII","N. Domingo Street","Mayor Ignacio Santos Diaz Street","Batasan–San Mateo Road","University Avenue","Rt. Rev. G. Aglipay Street","Sgt. Esguerra Avenue","Quirino Highway","Bonifacio Avenue","Tomas Arguelles Street","Doña Juana S. Rodríguez Avenue","Gil Puyat Avenue","Pedro B. Mendoza Street","Zamora Street","Arnaiz Avenue","Domingo M. Guevara Street","Edang Street","Alabang-Zapote Road","Diego Cera Avenue","10th Avenue","Alfaro Street","Alvarado Street","Amber Avenue","Angel Tuazon Avenue","Arayat Street","A. Raquiza Street","Arizona Street","Avenida Alfonso","Bálic-Bálic Road (Route 53)","Banahaw Street","Constitutional Road","Balara Airfield","Bohol Avenue","Brixton Hill Street","Broadway Avenue","Buendía Avenue","C. Ruiz Street","Calle Baltazar","Calle Pinagbarilan","Nicanor García Street","N. S. Amoranto Sr. Street", "José Gil Street","Danny Floro Street","Don Antonio Street","Mother Ignacia Street","Eraño Manalo Avenue","P. Tuazon Boulevard","A. Layug Street","Sgt. F. Santos","Batasan Road","Magalona Street","Bagong Silang Road","A. Soriano Street","M. Vicente Street","Sacred Heart Street","Quezon Avenue","F. Abello Street","ADB Avenue","Holy Spirit Drive","Carlos P. Garcia Avenue","M. L. Quezón","Elisco Road","F. Ortigas, Jr. Road","E. Rodríguez Sr. Avenue","Bagong Farmers Avenue","J. V. Alvior Street","Elpidio Quirino Avenue","F. B. Harrison Street","Florante Street","Farmers Avenue","Calle Reposo (Street)","Calle Retiro (Street)","Calle Valenzuela (Street)","Canley Road","Capitol Park Drive","Cebu Avenue","Central Avenue","Central Boulevard","Concepción Street","Coronado Street","Constitutional Hill Road","IBP Road","Cpl. York Street","Daang Bakal Road","Dalla Street","Dansalan Street","Dao Street Extension","Fairview Avenue","Don Mariano Marcos Avenue","Diamond Avenue","Don Antonio Street","Interneighborhood Street","East Avenue (U.P. Diliman)","East Manila South Road (Route 59)","Elizalde Road","Emerald Avenue","España Boulevard Extension","Elpidio Quirino Avenue","P. Binay Street","Sen. Jose O. Vera Street","Manotok Drive","Capitol Hills Drive","Dr. José P. Rizal Extension","Sofronio Veloso Street","Vicente A. Rufino Street","Tomas Castro Street","Lieutenant Artiaga Street","F. Santos Street","Quirino Highway","Kalayaan Avenue","Ibuna Street","Capt. S. Roja","Valenzuela Street","Shaw Boulevard","Luis Sianghio Street","T. Gener Street","Judge Jimenez Street","Jose Erestain Sr. Street","Dr. Jesus Azurin Street","Victor Medina St.","Carlos P. Garcia Avenue","Ernesto Porto Street","C. M. Recto Street","Bonifacio Avenue","F. B. Harrison Street","General Ricarte Street","Granada Street","Greenhills Drive","Guadalupe–Pateros Road (Route 21A)","H. Lozada Street (Aurora Blvd to 29 de Agosto)","Herrera Street","Highland Drive","Hollywood Drive","Ilang-Ilang Street",
        "Ipo Road","Don Tomas Susano Road","Joffre Street","Johnston Street","José Gil Street","José Rizal Boulevard (Route 54B)","Manila East Road","Kabihasnan St.","Katipunan Avenue","Kentucky Street","Richenine Street","Kitchener Street","La Loma-Balintawak Road","Sta. Catalina Street","Nicanor Roxas Street","P. Antonio Street","Dra. Leonisia H. Reyes Street","Payatas Road","Maayusin Street","	Justice Lourdes Paredes San Diego Avenue","Don Manuel Agregado","Don Ramon Street","C. Suarez Street","Mayaman Street","Marilág Streets","Samson Road","Epifanio de los Santos Avenue","MacArthur Highway","Marikina-Infanta Highway","Magsaysay Boulevard","Legarda Street","Aurora Boulevard","Highway 55","Manila Provincial Road","Malusog Street","Mapagkawanggawa Street","G. Ocampo Street","Mayumì Street","Taft Avenue Extension","V. Luna Avenue","Mindanao Avenue","M. de Salapan Street","Ermin Garcia Avenue","General Ordoñez Street","Lambay Street","Laong Laan Street","Lion's Road","Litex Road","Maganda Street","Main Avenue","Makiling Street","Malasimbo Street","Malibay Street","Maligaya Street","Malualhatì Street","Manila Circumferential Road","Manila North Road","Highway 55","Manila Provincial Road","Maningning Street","Mapagsanggalang Street","Marne Street","Matimyas Street","México Road","MIA Road","Matiisin Street","Mayor Rodriguez Avenue","Melanio Street","Minnesota Street","Molave Street","E. Maclang Street","Scout Oscar M. Alcaraz Street","Pablo P. Reyes, Sr. Street","F. Manalo Street","Lawton Avenue","Andrews Avenue","Osmeña Avenue","North Luzon Expressway","Doña M. Hemady Street","Atty. A. Mendoza Street","Arnaiz Avenue","Chino Roces Avenue","C. P. Garcia Avenue","Romualdez Street","Sen. Mariano J. Cuenco Street","Eagle Street","N. Ramirez Street","Quezon Avenue","Thailand Street","F. Manalo","Rizal Avenue Extension","Dr. Sixto Antonio Avenue","Sumulong Highway","Fernando Poe Jr. Avenue","Tomas Morato Avenue","Old Samson Road","P. de la Cruz Street","Col. Bonny Serrano Avenue","Morong Street","New York Avenue (west)","Nevada Street","Nichols-McKinley Road","North Diversion Road","Ortega Street","Pacific Avenue","Paraiso Street","Pasay Road (Route 57)","Pasong Tamo","P. Aunario Street","Pershing Street","Pi y Margall Street","Plaridel Street","Pulog Street","Quezon Boulevard Extension","Rada Street","Riverside Drive","Roosevelt Avenue","Sampaloc Avenue","Samson Road","San Bartolome Street","Santolan Road","Adevoso Street","Don Julio Gregorio Street","	Speaker Perez Street","Roxas Avenue","Timog Avenue","Eugenio Lopez Street","Scout Albano Street","Scout Bayoran Street","Scout Borromeo Street","Scout Madriñan Street","Scout Rallos Street","Scout Limbaga Street","Scout Fernandez Street","Scout Fuentebella Street","Scout Gandia Street","Scout de Guia Street","Dr. Lazcano Street","Scout Delgado Street","Scout Lozano Street","Scout Castor Street","Marathon Street","Fr. Martinez Street","Scout Ojeda Street","Scout Chuatoco Street","Scout Magbanua Street","Scout Reyes Street","Scout Santiago Street","Scout Tobias Street","San Venancio Street","Sauyo Road","Sierra Madre Street","Scout Tuason Street","Scout Torillo Street","Scout Ybardolaza Street","	11th Jamboree Street","Scout Rallos Extension","Osmeña Highway","Don Alejandro Roces Avenue","	Hunters ROTC Avenue","	Dr. Santos Avenue","La Campana Street","Eymard Drive","Comm. Dev. Center Street","Sgt. E. Rivera Street","N. Zamora Street","Exchange Road","Aurora Boulevard","Familara Street","Saint Paul Street","Betty Go-Belmonte Street","Reraon Street","Sgt. J. Catolos","Elpidio Quirino Avenue","emple Drive","South Superhighway","South Market Street","St. Francis Avenue","Súcat Road","Sultana Street","Sunnyside Drive","Tacio Street","Tagaytay Street","Tagaytay Street","Tektite Road","Tramo Street","Tuayan Street","Ugong Street","Valley Road","Verdun Street","Virginia Street","Zebra Drive"
        );

        $this->total_streetname = count($this->array_streetname)-1;
    }

    public function random_dates($start_date = null, $end_date = null){
        date_default_timezone_set('Asia/Singapore');
        $now =  date('Y-m-d H:i:s');
        // Convert to timetamps
        $min = $start_date == null ? 1577865600 : strtotime($start_date);
        $max = $end_date == null ? strtotime($now) : strtotime($end_date);

        // Generate random number using above bounds
        $val = mt_rand($min, $max);

        // Convert back to desired date format
        return date('Y-m-d H:i:s', $val);
    }

    public function nbi_expiration(){
        date_default_timezone_set('Asia/Singapore');
        // Convert to timetamps
        $min = strtotime('+6 months');
        $max = strtotime('+18 months');

        // Generate random number using above bounds
        $val = mt_rand($min, $max);

        // Convert back to desired date format
        return date('Y-m-d', $val);
    }


    public function generateHomeOwners(Request $request,Response $response, array $args){
        $total = CustomRequestHandler::getParam($request,"total");

        $total = $total == null ? 100 : $total;

        $hashedPass = "$2y$10$.jadlIJl4zr6D61QR2v1/O1Oy2O3B6UBZlamuA4eLVE3BYUhOWZAS";

        $lastMobile = $this->generateData->getLastMobileNumber();
        $lastMobile = $lastMobile['data']['phone_no'];
        $last_fiveDigits = substr($lastMobile, -5);
        $last_fiveDigits = $last_fiveDigits == "99999" ? "10000" : $last_fiveDigits;

        for($x=0;$x<$total;$x++){
            $fnameIndex = mt_rand(0,$this->total_fname);
            $lnameIndex = mt_rand(0,$this->total_lname);
    
            $fname = $this->array_fname[$fnameIndex];
            $lname = $this->array_lname[$lnameIndex];
    
                $last_fiveDigits++;
                $last_fiveDigits = $last_fiveDigits == "99999" ? "10000" : $last_fiveDigits;

            $phone = "092".(mt_rand(100,999)).($last_fiveDigits);

            $responseMessage = $this->homeowner->createHomeownerWithHashedPass(
            ucfirst($fname),
            ucfirst($lname),
            $phone,
            $hashedPass);
        }

        $lastUsers = $this->generateData->getUsersID($total);
        $lastUsers = $lastUsers['data'];
        $userID = null;

        for($x=0;$x<count($lastUsers);$x++){
            $userID = $lastUsers[$x]['user_id'];
            $userType = $this->generateData->getUserType($userID);
            $userType = $userType['data']['user_type_id'];
            $date = $this->random_dates();
            $test= $this->generateData->updateHomeownerCreateDate( $userID, $date,  $userType);
        }

        $resData = [];
        // $resData['fnametotal'] = $this->total_fname;
        // $resData['lnametotal'] = $this->total_lname;
        // $resData['randNum'] = rand(0,$this->total_fname);

        // $resData['first_name'] = $fname;
        // $resData['last_name'] = $lname ;
        // $resData['phone'] =  $phone;

        // $resData['total'] = $total;
        // $resData['lastMobile'] = $lastMobile;
        // $resData['lastfive'] = $last_fiveDigits;
        return $this->customResponse->is200Response($response, $resData);
    }
    
    public function changeUserCreateDate(Request $request,Response $response, array $args){
        $total = CustomRequestHandler::getParam($request,"total");
        $total = $total != null && is_numeric($total) ? $total : 10;
        $lastUsers = $this->generateData->getUsersID($total);
        $lastUsers = $lastUsers['data'];
        $resData = [];
        $userID = null;

        for($x=0;$x<count($lastUsers);$x++){
            $userID = $lastUsers[$x]['user_id'];
            $userType = $this->generateData->getUserType($userID);
            $userType = $userType['data']['user_type_id'];
            $date = $this->random_dates();
            $test= $this->generateData->updateHomeownerCreateDate( $userID, $date,  $userType);
        }

        $resData = [];
        // $resData['last_ID'] = $userID;
        // $resData['userType'] = $userType;
        // $resData['rand_date'] = $date;
        // $resData['test'] = $test;
        // 2020-06-09 15:29:40
        // $resData['last_users'] = $lastUsers;
        return $this->customResponse->is200Response($response, $resData );
    }
    




    public function extraAddressInfoGenerator(){
        $direction = array("turn right at the","go past the","turn left at the","go behind the","go in front of the","head towards the","go near the");
        $direction_tot = count($direction)-1;

        $landMark = array("pharmacy","nearby mall","school","abandoned building","main road","highway","fountain","rastaurant","movie theatre","hospital","library","carenderia","forest");
        $landMark_tot = count($landMark)-1;

        $area = array("left","right","front","back");

        $area2 = array("near","behind","in front","to the left of","to the right of");

        // $this->array_streetname[mt_rand(0,$this->total_streetname)];
        $hasExtraAddress = mt_rand(1,100);
        if($hasExtraAddress%3==0){
            return "Located ".$area2[mt_rand(0,4)]." the ".$landMark[mt_rand(0,$landMark_tot)].". ".ucfirst($direction[mt_rand(0,$direction_tot)])." ".$landMark[mt_rand(0,$landMark_tot)]." then ".$direction[mt_rand(0,$direction_tot)]." street named ".$this->array_streetname[mt_rand(0,$this->total_streetname)].". Destination is located at the ".$area[mt_rand(0,3)].".";
        }
        if($hasExtraAddress%2==0){
            return "Located near the ".$landMark[mt_rand(0,$landMark_tot)].". ".ucfirst($direction[mt_rand(0,$direction_tot)])." ".$landMark[mt_rand(0,$landMark_tot)]." then ".$direction[mt_rand(0,$direction_tot)]." street named ".$this->array_streetname[mt_rand(0,$this->total_streetname)].". Destination is located at the ".$area[mt_rand(0,3)].".";
        }
        return "";
    }

    public function addHomesToHomeowners(Request $request,Response $response, array $args){
        $total = CustomRequestHandler::getParam($request,"total");
        $city = CustomRequestHandler::getParam($request,"city");
        $total = $total != null && is_numeric($total) ? $total : 10;
        $lastHomeowners = $this->generateData->gethomeownersID($total);
        $lastHomeowners =   $lastHomeowners['data'];
        $resData = [];
        $userID = null;
        $barangays = [];
        $barangaysIndex = 0;

        $totalAddresses = 1;

        //Get Barangays per city
        if($city != null){
            $barangays = $this->generateData->getbarangayspercity( $city );
            $barangays = $barangays['data'];
        }

        for($x=0;$x<$total;$x++){
            $userID = $lastHomeowners[$x]['user_id'];

            $totalAddresses = mt_rand(1,4);

            for($y=0; $y<$totalAddresses; $y++){
                if($city == null){
                    $city = mt_rand(1,12);
                    $barangays = $this->generateData->getbarangayspercity($city);
                    $barangays = $barangays['data'];
                    $city = null;
                }

                $barangaysIndex = mt_rand(1,count($barangays)-1);
                $streetNameIndex = mt_rand(0,$this->total_streetname);
        
                $street_no = mt_rand(1,120);
                $street_name=  $this->array_streetname[$streetNameIndex];
                $barangay_id=$barangays[$barangaysIndex]['id']; 
                $home_type=mt_rand(1,6);
                $extra_address_info=$this->extraAddressInfoGenerator();
        
                // adds address
                $result = $this->file->saveAddress($userID, $street_no, $street_name, $barangay_id, $home_type, $extra_address_info);
            }
        }
       


        // $resData['lastHomeonwers'] =   $lastHomeowners;
        // $resData['userID'] = $userID;
        // $resData['streetno'] = $street_no;
        // $resData['street_name'] = $street_name;
        // $resData['barangay_id'] = $barangay_id;
        // $resData['home_type'] = $home_type;
        // $resData['extra_address_info'] = $extra_address_info;
        // $resData['barangays'] = $barangays;

        // $resData['barangayIndex'] = $barangaysIndex;
        // $resData['barangayID'] =  $barangay_id;
        // $resData['cityID'] = $city;

        return $this->customResponse->is200Response($response, $resData );
    }







    public function generateWorkers(Request $request,Response $response, array $args){

        $total = CustomRequestHandler::getParam($request,"total");

        $total = $total == null ? 100 : $total;

        $hashedPass = "$2y$10$.jadlIJl4zr6D61QR2v1/O1Oy2O3B6UBZlamuA4eLVE3BYUhOWZAS";

        $lastMobile = $this->generateData->getLastMobileNumber();
        $lastMobile = $lastMobile['data']['phone_no'];
        $last_fiveDigits = substr($lastMobile, -5);
        $last_fiveDigits = $last_fiveDigits == "99999" ? "10000" : $last_fiveDigits;

        for($x=0;$x<$total;$x++){
            $fnameIndex = mt_rand(0,$this->total_fname);
            $lnameIndex = mt_rand(0,$this->total_lname);
    
            $fname = $this->array_fname[$fnameIndex];
            $lname = $this->array_lname[$lnameIndex];
    
                $last_fiveDigits++;
                $last_fiveDigits = $last_fiveDigits == "99999" ? "10000" : $last_fiveDigits;

            $phone = "092".(mt_rand(100,999)).($last_fiveDigits);

            $responseMessage = $this->worker->createWorker(
                    ucfirst($fname),
                    ucfirst($lname),
                    $phone,
                    $hashedPass);
        }

        $lastUsers = $this->generateData->getUsersID($total);
        $lastUsers = $lastUsers['data'];
        $userID = null;

        for($x=0;$x<count($lastUsers);$x++){
            $userID = $lastUsers[$x]['user_id'];
            $userType = $this->generateData->getUserType($userID);
            $userType = $userType['data']['user_type_id'];
            $date = $this->random_dates();
            $test= $this->generateData->updateHomeownerCreateDate( $userID, $date, $userType);
        }

        $resData = [];
        // $resData['fnametotal'] = $this->total_fname;
        // $resData['lnametotal'] = $this->total_lname;
        // $resData['randNum'] = rand(0,$this->total_fname);

        // $resData['first_name'] = $fname;
        // $resData['last_name'] = $lname ;
        // $resData['phone'] =  $phone;

        // $resData['total'] = $total;
        // $resData['lastMobile'] = $lastMobile;
        // $resData['lastfive'] = $last_fiveDigits;
        return $this->customResponse->is200Response($response, $resData);
    }


    function generate_string($strength = 8, $input = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789') {
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
     
        return $random_string;
    }

    public function completeWorkerRegistration(Request $request,Response $response, array $args){
        $total = CustomRequestHandler::getParam($request,"total");

        $total = $total == null ? 100 : $total;
        $lastWorkers = $this->generateData->getWorkersID($total, true);
        $lastWorkers =   $lastWorkers['data'];
        $skills_data = [];
        $skills_data["skills_toDelete"] = [];
        $skills_data["skills_toUpdate"] = [];
        $totalSkills = 0;
        $skillMain = 1;
        $cities_toDelete = [];
        $cityMain = CustomRequestHandler::getParam($request,"city");
        $citylock = true;
        $totalCities = 1;
        if($cityMain == null){
            $citylock = false;
            $cityMain = 1;
            $totalCities = 12;
        }



        $cities_toAdd = [];
        $total_preferred_cities = mt_rand(1, 100);
        if($total_preferred_cities % 2 == 0 ){
            $total_preferred_cities = mt_rand(1, 4);
        } else if($total_preferred_cities % 3 == 0){
            $total_preferred_cities = mt_rand(3, 4);
        } else if ($total_preferred_cities % 7 == 0){
            $total_preferred_cities = mt_rand(3, 5);
        } else if ($total_preferred_cities % 5 == 0){
            $total_preferred_cities = mt_rand(1, 12);
        }else {
            $total_preferred_cities = mt_rand(1, 6);
        }




        // $userID =  $lastWorkers[0]['user_id'];

        

        // // // for starts here
        for($z=0; $z< $total; $z++){

            $userID =  $lastWorkers[$z]['user_id'];

            // // SAVE CITIES
            $cityMinus = range(1,12);
            shuffle($cityMinus);
            //2 <= 1
            while($total_preferred_cities >= count($cities_toAdd)){
                if(count($cities_toAdd)==0){ // ensures that at least one of the cities is a set city 1-12
                    array_push($cities_toAdd, $cityMain);
                    unset( $cityMinus[array_search($cityMain, $cityMinus)]);
                }else{
                    array_push($cities_toAdd,array_shift($cityMinus));
                }
            }
            sort($cities_toAdd);
            $ModelResponse = $this->worker->savePreferredCities_intoDB($userID, $cities_toAdd, $cities_toDelete);


            // // SAVE SKILLS
            $skills_data["skills_toAdd"] = []; // array of 1-6
            $skills_list_minus = range(1,6);
            shuffle($skills_list_minus);

            $totalSkills = mt_rand(1,100); // random total number of skills per worker
            if($totalSkills % 2 == 0 || $totalSkills % 7 == 0){
                $totalSkills = mt_rand(1,3);
            } else if ($totalSkills % 3 == 0 ){
                $totalSkills = mt_rand(2,4); // higher probability for a worker to have 2 skills
            } else {
                $totalSkills = mt_rand(2,6); // less probability for a worker to have all 6 skills
            }
    
            for($x=0;$x<$totalSkills;$x++){
                if($x==0){ // ensures that at least one of the skills is a set number 1-6
                    array_push($skills_data["skills_toAdd"],$skillMain);
                    unset($skills_list_minus[array_search($skillMain,$skills_list_minus)]);
                    $skillMain++;
                    $skillMain = $skillMain == 7 ? 1 : $skillMain;
                }else{
                    array_push($skills_data["skills_toAdd"],array_shift($skills_list_minus));
                }
            }
            sort($skills_data["skills_toAdd"]);
    
    
            $default_rate_type = mt_rand(1,100); 
            if($default_rate_type % 2 == 0 || $default_rate_type % 7 == 0){
                $default_rate_type = 2;
            } else if ($default_rate_type % 3 == 0 ){
                $default_rate_type = mt_rand(1,2); // higher probability for rt to be daily
            } else {
                $default_rate_type = mt_rand(1,4); // less probability for rt to be project based
            }
    
            $default_rate = 300;
            $rate_max_index = 0;
            $rates = [];
            switch($default_rate_type){
                case 1:
                    $rates = array(50,55,60,65,70,75,80,85,90,95,100);
                    break;
                case 3:
                    $rates = array(1250,1300,1350,1400,1450,1500,1550,1600,1650,1600,1750,1700,1850,1800,1950,2000);
                    break;
                case 4:
                    $rates = array(1650,1600,1750,1700,1850,1800,1950,2000,2500,2750,3000,3250,3500);
                    break;
                default:
                $rates = array(300,350,400,450,500,550,600,650,700);
            }
            $rate_max_index = count($rates)-1;
            $default_rate = $rates[mt_rand(0, $rate_max_index)]; 
    
            $clearance_no = $this->generate_string()."-".$this->generate_string();
    
            $expiration_date = $this->nbi_expiration();
    
            // Add our collected and processed data into our custom function we wrote in the worker model
            $ModelResponse = $this->worker->save_personalInformation(
                $userID,  
                $skills_data, 
                $default_rate, 
                $default_rate_type, 
                $clearance_no, 
                $expiration_date,
                false ,  
                "477dfc4c115bab919abd877e3fb3981a9435576c4a659624dc8e5e4d98c2d389b4a260a12c5b5540bbed0dbc323d205733cc.png",   
                "https://storage.googleapis.com/nbi-photos/",  
                null
            );


            // // Save User
            $result = $this->worker->completeWorkerRegistration($userID);


        // // Update support ticket
        $creationDate = $this->generateData->getUserCreationDate($userID);
        $creationDate = $creationDate['data']['created_on'];

        $updateTicket = $this->generateData->updateSupportTicketDate($userID, $creationDate);
        // // //  for ends here
        }





        $resData = [];
        // $resData["lastWorkers"] = $lastWorkers;
        // $resData["userID"] = $userID;
        // $resData["creationDate"] = $creationDate;
        // $resData["result"] = $result;
        // $resData["skills_data"] = $skills_data;
        // $resData["default_rate_type"] = $default_rate_type;
        // $resData["default_rate"] = $default_rate;
        // $resData["clearance_no"] = $clearance_no;
        // $resData["expiration_date"] = $expiration_date;
        // $resData["cities_toAdd"] = $cities_toAdd;
        // $resData["bool"] = $total_preferred_cities >= count($cities_toAdd);

        return $this->customResponse->is200Response($response, $resData);
    }













    public function generateSupportAgents(Request $request,Response $response, array $args){
        $total = CustomRequestHandler::getParam($request,"total");
        $role = CustomRequestHandler::getParam($request,"role");
        $sup = CustomRequestHandler::getParam($request,"sup");        
        $create_date = CustomRequestHandler::getParam($request,"create_date");

        $total = $total == null ? 100 : $total;
        $role =  $role == null ? 1 : $role;

        //check if sup is in db

        $hashedPass = "$2y$10$.jadlIJl4zr6D61QR2v1/O1Oy2O3B6UBZlamuA4eLVE3BYUhOWZAS";

        $lastMobile = $this->generateData->getLastMobileNumber();
        $lastMobile = $lastMobile['data']['phone_no'];
        $last_fiveDigits = substr($lastMobile, -5);
        $last_fiveDigits = $last_fiveDigits == "99999" ? "10000" : $last_fiveDigits;

        for($x=0;$x<$total;$x++){
            $fnameIndex = mt_rand(0,$this->total_fname);
            $lnameIndex = mt_rand(0,$this->total_lname);
    
            $fname = $this->array_fname[$fnameIndex];
            $lname = $this->array_lname[$lnameIndex];
    
                $last_fiveDigits++;
                $last_fiveDigits = $last_fiveDigits == "99999" ? "10000" : $last_fiveDigits;

            $phone = "092".(mt_rand(100,999)).($last_fiveDigits);

            $responseMessage = $this->homeowner->createHomeownerWithHashedPass(
            ucfirst($fname),
            ucfirst($lname),
            $phone,
            $hashedPass);
        }

        $lastUsers = $this->generateData->getUsersID($total);
        $lastUsers = $lastUsers['data'];
        $userID = null;
        $date = $create_date;
        // $userID = $lastUsers[0]['user_id'];

        for($x=0;$x<count($lastUsers);$x++){
            $userID = $lastUsers[$x]['user_id'];
            $email = strtolower(substr($lastUsers[$x]['first_name'],0,1).$lastUsers[$x]['last_name']."@support.com");
            
            // ensure same email does not appear twice in db
            $isEmailInDB = $this->generateData->getSupportEmail($email);
            $isEmailInDB = !($isEmailInDB['data'] == false);
            $h = 2;
            while($isEmailInDB==true){
                $email = strtolower(substr($lastUsers[$x]['first_name'],0,1).$lastUsers[$x]['last_name'].$h."@support.com");
                $isEmailInDB = $this->generateData->getSupportEmail($email);
                $isEmailInDB = !($isEmailInDB['data'] == false);
                $h++;
            }

            if($create_date == null){
                $date = $this->random_dates("2021-12-16 03:02:00", "2022-01-01 9:00:00");
            }
            
            $result = $this->generateData->createSupport($userID, $role, $date, $email, $sup);

        }

        $resData = [];
        // $resData['userID'] = $userID;
        // $resData['result'] =  $result;
        // $resData['createDate'] =  $date;
        // $resData['role'] =  $role;
        // $resData['email'] =  $email;
        // $resData['isEmailInDB'] =  $isEmailInDB;

        // $resData['fnametotal'] = $this->total_fname;
        // $resData['lnametotal'] = $this->total_lname;
        // $resData['randNum'] = rand(0,$this->total_fname);
        // $resData['first_name'] = $fname;
        // $resData['last_name'] = $lname ;
        // $resData['phone'] =  $phone;
        // $resData['total'] = $total;
        // $resData['lastMobile'] = $lastMobile;
        // $resData['lastfive'] = $last_fiveDigits;
        return $this->customResponse->is200Response($response, $resData);
    }

































    public function test(Request $request,Response $response, array $args){
        // $resData = [];
        // $resData['anouncement'] =  $announce['data'];
    
        // $resData['myRole'] = $user_role;
        return $this->customResponse->is200Response($response,"This route works");
    }
}

