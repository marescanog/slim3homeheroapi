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

        $this->badWorkersList = array(466, 443, 191, 428, 169, 463, 479, 451);

        $this->vowels = array("a","e","i","o","u");

        $this->willling_syn = array("willing","ready","happy","keen","prepared");

        $this->help_synonyms_noun = array("help","assistance","help","aid","support","help",
        "service","guidance","help","a helping hand","some help","some assistance","guide","helping out");

        $this->help_synonyms_verb = array("help","assist","help","aid","support","guide","help","service","help");

        $this->need_synonyms_past = array("needed","wanted","desired","needed","wanted","called for","needed","wanted", "required","needed");

        $this->need_synonyms = array("need","want","desire","need","am in need of","have need of","want","call for","need", "necessitate", "require","need");

        $this->need_synonyms_obj = array("needs","has need of","calls for", "necessitates", "requires","needs");

        $this->time_period = array('Almost daily','On occasion','Every once in a while','At times','Every so often','Oftentimes','From time to time','Occasionally','Sometimes','Everyday','Every week','Every month','Every year');
        
        $this->familymember = array(
            "brother","friend","mother","sibling","father","uncle","aunt","sister"
        );

        $this->noun1 = array('the storm',"my ".($this->familymember[mt_rand(0,count($this->familymember)-1)]),'the heavy rains','my neighbor','a passerby','the rain','my kids','some wild animals',
        'my children',"the guest","my ".($this->familymember[mt_rand(0,count($this->familymember)-1)]), "the neighbor's kids", 'the dog', 'my dog', "the neighbor's dog", "some birds", "some rats",
        "some person","some kid","a stranger","my ".($this->familymember[mt_rand(0,count($this->familymember)-1)])
        );

        $this->negative_action = array('destroys','breaks','accidentally destroys','messes up',
        'dirties','damages','wrecks','breaks up','ruins'
        );
        $this->negative_feeling = array('helpless','angry','sad','mad','disappoinated','annoyed','irritated','exasperated','displeased','unhappy','downhearted',
        'frustrated','a lot of rage','bitter','mixed up','confused','overwhelmed','tired');

        $this->positive_feeling = array('appreciative','grateful','happy','overjoyed','thankful','joyful',
            'satisfied','cheerful','content'
        );

        $this->hope_syn = array(
            "hoping","expecting","anticipating","foreseeing","counting","banking"
        );

        $this->perform_job_syn = array(
            "done","acted on","dealt with","finished","completed","fulfilled","accomplished"
        );

        $this->quicktime_syn = array(
            "soon","shortly","presently","in the near future","quickly","in a timely manner","on the double"
        );

        $this->quicktime_syn2 = array(
            "instantly","now","soon","presently","quickly","in a timely manner","on the double",
            "correctly","right","properly","quickly","fully","completely","precisely",
            "well","nicely"
        );

        $this->search_syn = array(
            "searching","looking for","hunting","seeking","asking","requesting"
        );

        $this->worker_syn = array(
            "worker","hireling","laborer","helper","hand help","workman","worker","hired hand","hired man"
        );

        $this->job_syn = array(
            "job","task","posting","chore","project","proposal","matter","affair","matter at hand","task at hand","job at hand"
        );

        $this->correctly_syn = array(
            "correctly","right","properly","entirely","quickly","fully","completely","exactly","precisely",
            "well","nicely"
        );

        $this->important_syn = array(
            "means a lot","is important","is significant","matters","is serious","is weighty","is crucial"
        );

        $this->qualified_syn = array(
            "qualified","skilled","masterful","good","competent","knowledgeable","trained","fit","equipped","expert","proficient","skillful"
        );

        $this->action_do = array(
            "do","perform","complete","carry out","undertake","accomplish","fulfill","do"
        );

        $this->excuses = array(
            "attending a wedding","going on a business trip",
            "having guests over","taking a vacation","hosting a party",
            "leaving for an extended work trip","having my anniversary",
            "having a birthday party", "going on vacation","attending an important event",
            "going to a party","hosting a special event"
        );

        $this->past = array(
            "last","prior","former","past","previous"
        );

        $this->excuse_timeframe = array(
            "next week","soon","in the future","next month","months from now",
            "two weeks from now","three months from now","three weeks from now",
            "in the near future","very soon"
        );

        $this->badReview = array(
            "was a little bit dissappointing", "was not able to do a good job","did not meet my expectations",
            "was not able to finish it","could not complete it","had some issues",
            "poorly did the task","performed badly"
        );

        $this->timework = array(
            "on weekends","on weekdays","in the morning","in the afternoon","in the evening",
            "every other day","most nights","most days","most afternoons",
            "some nights","some days","some afternoons","some mornings",
            "most mornings"
        );

        $this->personactivity = array(
            "work","am busy with my business","do my job","go to my job",
            "work remotely", "am busy", "go out", "do errands",
            "attend school","attend class"
        );


        $this->compromise = array(
            "but I can adjust my schedule if need be",
            "but it is easy for me to move my schedule",
            "but I can move my schedule if needed",
            "but I can have my ".($this->familymember[mt_rand(0,count($this->familymember)-1)])." supervise while I am away",
            "but I can make adjustments","but I am flexible with time",
            "so I not flexible with time", "so I cannot adjust my schedule",
            "so it is hard for me to move my schedule",
            "so I cannot make adjustments","so I need someone who can adjust to my schedule"
        );
        $this->irrelevent_action = array(
            "The ".($this->past[mt_rand(0,count($this->past)-1)])." ".($this->worker_syn[mt_rand(0,count($this->worker_syn)-1)])
            ." ".($this->badReview[mt_rand(0,count($this->badReview)-1)]),

            "I will be ".($this->excuses[mt_rand(0,count($this->excuses)-1)])." "
            .($this->excuse_timeframe[mt_rand(0,count($this->excuse_timeframe)-1)]),

            "I ".($this->personactivity[mt_rand(0,count($this->personactivity)-1)])
            ." ".($this->timework[mt_rand(0,count($this->timework)-1)])." "
            .($this->compromise[mt_rand(0,count($this->compromise)-1)])

        );

        $this->worker_action = array(
            "I was ".($this->hope_syn[mt_rand(0,count($this->hope_syn)-1)])
            ." that the job can be ".($this->perform_job_syn[mt_rand(0,count($this->perform_job_syn)-1)])
            ." ".$this->quicktime_syn[mt_rand(0,count($this->quicktime_syn)-1)],
            
            "I am ".($this->search_syn[mt_rand(0,count($this->search_syn)-1)])
            ." for a ".($this->worker_syn[mt_rand(0,count($this->worker_syn)-1)])
            ." who can ".($this->action_do[mt_rand(0,count($this->action_do)-1)])." the ".($this->job_syn[mt_rand(0,count($this->job_syn)-1)])
            ." ".($this->correctly_syn[mt_rand(0,count($this->correctly_syn)-1)]),
            
            "this ".($this->important_syn[mt_rand(0,count($this->important_syn)-1)])." to me so the ".($this->job_syn[mt_rand(0,count($this->job_syn)-1)])
            ." should be ".($this->correctly_syn[mt_rand(0,count($this->job_syn)-1)])." done",
            
            "I need someone ".($this->qualified_syn[mt_rand(0,count($this->qualified_syn)-1)])." to "
            .($this->action_do[mt_rand(0,count($this->action_do)-1)])." the ".($this->job_syn[mt_rand(0,count($this->job_syn)-1)])
        );

        $this->objectArr = array( // array()
            array('pipes','kitchen pipes','bathroom pipes','kitchen faucet','bathroom faucet','faucet','outdoor pipes','outdoor faucet'), // 'General Plumbing Project'
            array('gate','kitchen area','garden shed','wooden box','storage area'), // 'General Carpentry Project',
            array('lawn','yard','garden','flower bed','vegetable garden', 'flower garden','front yard','back yard'), // 'General Gardening Project',
            array('kitchen lights','bathroom lights','bedroom lights','living room lights','house wiring'), // 'General Electrical Project',
            array('guest room','garage','living room','kitchen','pantry','storage area',"kid's playroom",'house design','attic','bedroom','bathroom','game room','nursery','closet area','master bedroom'), // 'General Home Improvement Project',
            array('guest room','garage','house','back yard','front yard','gutter','curtains','clothes','sofa','living room','kitchen','pantry','storage area',"kid's playroom",'house design','attic','bedroom','bathroom','game room','nursery','closet area','master bedroom'), // 'General Cleaning Project',
            array('main bathroom sink','guest bathroom sink','bathroom sink','sink'), // 'Bathroom Sink Installation',
            array('main bathroom sink','guest bathroom sink','bathroom sink','sink'), // 'Bathroom Sink Repair',
            array('guest tub','guest bathtub','main tub','main bathtub', 'tub','bathtub'), // 'Bathtub Repair',
            array('drainage','outdoor drain','shower drain','kitchen drain','garage drain'), // 'Drainage Installation',
            array('drainage','outdoor drain','shower drain','kitchen drain','garage drain'), // 'Drainage Repair',
            array('bathroom sink','sink','kitchen sink','outdoor sink', 'garage room sink', 'guest room sink'), // 'General Sink Installation',
            array('bathroom sink','sink','kitchen sink','outdoor sink', 'garage room sink', 'guest room sink'), // 'General Sink Repair',
            array('outdoor kitchen sink','main kitchen sink','guest kitchen sink','kitchen sink','sink'), // 'Kitchen Sink Installation',
            array('outdoor kitchen sink','main kitchen sink','guest kitchen sink','kitchen sink','sink'), // 'Kitchen Sink Repair',
            array('main bathroom pipe','guest bathroom pipe','bathroom pipe','outdoor kitchen pipe','main kitchen pipe','guest kitchen pipe','kitchen pipe','pipe'), // 'Pipe Installation',
            array('main bathroom pipe','guest bathroom pipe','bathroom pipe','outdoor kitchen pipe','main kitchen pipe','guest kitchen pipe','kitchen pipe','pipe'), // 'Pipe Repair',
            array('main bathroom shower','guest bathroom shower','bathroom shower','shower'), // 'Shower Repair',
            array('main bathroom toilet','guest bathroom toilet','bathroom toilet','toilet'), //array(), // 'Toilet Installation',
            array('main bathroom toilet','guest bathroom toilet','bathroom toilet','toilet'), //'Toilet Repair',
            array('antique dining table', 'antique table', 'antique sofa','antique chair','antique cabinet','antique rocking chair', 'antique furniture','antique dresser'), //'Antique Furniture Restoration',
            array('road','driveway','front house pavement','pathway','back yard','front yard'), //'Asphalt Paving Service',
            array('balcony','porch','stoop','veranda'), //'Balcony Remodelling',
            array('cabinet', 'cupboard', 'drawer','closet','locker','sideboard'), //'Cabinet Assembly',
            array('cabinet', 'cupboard', 'drawer','closet','locker','sideboard'), //'Cabinet Construction',
            array('cabinet', 'cupboard', 'drawer','closet','locker','sideboard'), //'Cabinet Making',
            array('cabinet', 'cupboard', 'drawer','closet','locker','sideboard'), //'Cabinet Repair',
            array('bedroom window','bedroom windows','living room window','living room windows','guest room window','guest room windows','window','windows'), //'Caulking',
            array('closet room','closet','locker','sideboard'), //'Closet Construction',
            array('closet room','closet','locker','sideboard'), //'Closet Renovation',
            array('outdoor kitchen counter','outdoor kitchen countertop','kitchen counter','kitchen countertop','counter','countertop','worktop', 'garage worktop', 'outdoor worktop'), //'Countertop Installation',
            array('outdoor kitchen counter','outdoor kitchen countertop','kitchen counter','kitchen countertop','counter','countertop','worktop', 'garage worktop', 'outdoor worktop'), //'Countertop Repair',
            array('garage door','back door','screen door','side door','main door','door'), //'Door Installation',
            array('garage door','back door','screen door','side door','main door','door'), //'Door Repair',
            array('fence','gate','gateway','railing','hedge','wall', 'wooden gate', 'wooden fence'), //'Fence Installation',
            array('fence','gate','gateway','railing','hedge','wall', 'wooden gate', 'wooden fence'), //'Fence Repair',
            array('foam roofing','roofing insulation','insulation'), //'Foam Roofing Installation',
            array('foam roofing','roofing insulation','insulation'), //'Foam Roofing Repair',
            array('bookshelf','mantelshelf','shelving','dresser','IKEA furniture','furniture','cupboard','shelves','kitchen set','cabinet','sofa','table','dining set'), //'Furniture Assembly',
            array('bookshelf','mantelshelf','shelving','dresser','IKEA furniture','furniture','cupboard','shelves','kitchen set','cabinet','sofa','table','dining set'), //'Furniture Repair',
            array('bookshelf','mantelshelf','shelving','dresser','IKEA furniture','furniture','cupboard','shelves','kitchen set','cabinet','sofa','table','dining set'), //'Furniture Restoration',
            array('boxed roller door','overhead door','garage door'), //'Garage Door Installation',
            array('boxed roller door','overhead door','garage door'), //'Garage Door Repair',
            array('metal gate','overhead gate','gate','wooden gate'), //'Gate Installation',
            array('metal gate','overhead gate','gate','wooden gate'), //'Gate Repair',
            array('home','house','stuff','furniture','doors','windows'), //'General Home Repair Service',
            array('home','house','rooms'), //'Home Remodelling',
            array('home insulation','insulation','wall insulation'), //'Insulation Installation',
            array('home insulation','insulation','wall insulation'), //'Insulation Repair',
            array('locks','latch','bolt'), //'Locksmith Service',
            array('roof','ceiling','canopy'), //'Roof Construction',
            array('roof','ceiling','canopy'), //'Roof Repair',
            array('bookshelves','shelves','bookshelf','mantelshelf','shelving'), //'Shelf Assembly',
            array('bookshelves','shelves','bookshelf','mantelshelf','shelving'), //'Shelf Construction',
            array('bookshelves','shelves','bookshelf','mantelshelf','shelving'), //'Shelf Installation',
            array('tile','shingle','slate','brick'), //'Tile Installation',
            array('tile','shingle','slate','brick'), //'Tile Repair',
            array('floor','flooring','mezzanine','living room floor','guest room floor'), //'Vinyl Flooring Installation',
            array('floor','flooring','mezzanine','living room floor','guest room floor'), //'Vinyl Flooring Repair',
            array('metal gate','window bars','window grate','metal grate','fence','metal fence'), //'Welding Services',
            array('floor','flooring','mezzanine','living room floor','guest room floor'), //'Wood Flooring Installation',
            array('floor','flooring','mezzanine','living room floor','guest room floor'), //'Wood Flooring Repair',
            array('window-type aircon','window-type airconditioning','inverter aircon','inverter airconditioning','aircon','airconditioning'), //'(Aircon) Air Conditioner Installation',
            array('window-type aircon','window-type airconditioning','inverter aircon','inverter airconditioning','aircon','airconditioning'), //'(Aircon) Air Conditioner Maintenance',
            array('fridge','chiller','refrigerator','cooler','icebox','cold storage','cold storage box'), //'(Fridge) Refridgerator Repair',
            array('fridge','chiller','refrigerator','cooler','icebox','cold storage','cold storage box'), //'(TV) Television Repair',
            array('TV set','television set','television','TV'), //'(TV) Television Mounting',
            array('carbon monoxide alarm','carbon monoxide alarm system','fire alarm system', 'security system','security alarm system','security alarm','fire alarm', 'alarm system'), //'Alarm System Installation',
            array('dryer','washing machine','radio','instapot','fryer','air fryer','vitamix','food processor','blender','water heater','microwave','toaster','kitchen aid mixer','expresso machine','expresso maker','coffee maker','mixer','induction oven','induction stove', 'gas oven','gas stove'.'electric oven','electric stove','oven','stove'), //'Appliance Repair',
            array('backup generator','generator'), //'Backup Generator Installation',
            array('house wiring','living room wiring','electrical','wire','cable','cable and wire'), //'Cable and Wire Installation',
            array('house wiring','living room wiring','electrical','wire','cable','cable and wire'), //'Cable and Wire Repair',
            array('cellphone','phone','smartphone','iPhone','android phone','vivo phone','xiaomi'), //'Cellphone Repair',
            array('cellphone','phone','smartphone','iPhone','android phone','vivo phone','xiaomi'), //'Smartphone Repair',
            array('lamp','kitchen lights','bathroom lights','bedroom lights','living room lights','house wiring'), //'Electrical Lighting Installation',
            array('lamp','kitchen lights','bathroom lights','bedroom lights','living room lights','house wiring'), //'Electrical Lighting Repair',
            array('notebook','gaming laptop','laptop','iPad','tablet','cellphone','phone','smartphone','iPhone','android phone','vivo phone','xiaomi'), //'Gadget Repair',
            array('dryer','washing machine','radio','instapot','fridge','chiller','refrigerator','cooler','icebox','cold storage','cold storage box','fryer','air fryer','vitamix','food processor','blender','water heater','microwave','toaster','kitchen aid mixer','expresso machine','expresso maker','coffee maker','mixer','induction oven','induction stove', 'gas oven','gas stove'.'electric oven','electric stove','oven','stove'), //'General Gadget/Appliance Repair Service',
            array('backup generator','generator'), //'Generator Installation',
            array('backup generator','generator'), //'Generator Repair',
            array('notebook','gaming laptop','laptop'), //'Laptop/Computer Repair',
            array('roaster','microwave','electric microwave'), //'Microwave Repair',
            array('solar panels','solar panel'), //'Solar Panel Mounting',
            array('solar panels','solar panel'), //'Solar Panel Repair',
            array('house wiring','living room wiring','electrical','wire','cable','cable and wire'), //'Wiring Installation',
            array('house wiring','living room wiring','electrical','wire','cable','cable and wire'), //'Wiring Repair',
            array('hedge','bush','shrub','undergrowth','shrubbery'), //'Bush/Hedge Decoration',
            array('lawn','court','courtyard','garden','yard','backyard','front yard'), //'Clearing Garden',
            array('lawn','court','courtyard','garden','yard','backyard','front yard'), //'Garden Landscaping',
            array('lawn','court','courtyard','garden','yard','backyard','front yard'), //'Grass/Lawn Mowing',
            array('basketball court','lawn','court','courtyard','garden','yard','backyard','front yard'), //'Grounds Maintenance',
            array('hedge','bush','shrub','undergrowth','shrubbery'), //'Hedge Trimming',
            array('lawn','court','courtyard','garden','yard','backyard','front yard'), //'Lawn Care Service',
            array('tree','fruit tree','front yard tree','back yard tree','courtyard tree'), //'Tree Service',
            array('lawn','yard','garden','flower bed','vegetable garden', 'flower garden','front yard','back yard','lawn','court','courtyard','garden','yard','backyard','front yard'), //'Weeding Garden',
            array('guest room','garage','living room','kitchen','pantry','storage area',"kid's playroom",'house design','attic','bedroom','bathroom','game room','nursery','closet area','master bedroom','home','house'), //'Aparment/Home Decoratoring',
            array('guest room','garage','living room','kitchen','pantry','storage area',"kid's playroom",'house design','attic','bedroom','bathroom','game room','nursery','closet area','master bedroom','home','house'), //'Apartment/Home Interior Designer',
            array('house','home','mural','wall peice','wall'), //'Decorative Painters',
            array('house','home','mural','wall peice','wall'), //'Exterior Painting',
            array('house','home','mural','wall peice','wall'), //'Mural Painting',
            array('house','home','mural','wall peice','wall'), //'General Home Painting',
            array('window-type aircon','window-type airconditioning','inverter aircon','inverter airconditioning','aircon','airconditioning'), //'(Aircon) Air Conditioner Cleaning',
            array('air duct','vent','air vent'), //'Air Duct Cleaning',
            array('furniture','guest room','garage','house','back yard','front yard','gutter','curtains','clothes','sofa','living room','kitchen','pantry','storage area',"kid's playroom",'house design','attic','bedroom','bathroom','game room','nursery','closet area','master bedroom'), //'Apartment Moving Out Cleaning',
            array('mould','basement mould'), //'Basement Mould Removal',
            array('guest bathroom','main bathroom','guest tub','guest bathtub','main tub','main bathtub', 'tub','bathtub','main bathroom toilet','guest bathroom toilet','bathroom toilet','toilet','main bathroom shower','guest bathroom shower','bathroom shower','shower'), //'Bathroom Cleaning',
            array('carpet','rug','mat'), //'Carpet Cleaning',
            array('carpet','rug','mat'), //'Carpet Steam Cleaning',
            array('bookshelves','shelves','bookshelf','mantelshelf','shelving','guest room','garage','living room','kitchen','pantry','storage area',"kid's playroom",'house design','attic','bedroom','bathroom','game room','nursery','closet area','master bedroom','home','house'), //'Cleaning and Organizing Service',
            array('curtain','blinds','drop curtain','draping'), //'Curtain Cleaning',
            array('floor','flooring','mezzanine','living room floor','guest room floor','floor','living room floor','dining room floor',), //'Floor Care Service',
            array('gutter','drainpipes','rainspouts','trough'), //'Gutter Cleaning',
            array('area has a spider infestation that', 'area has a rat infestation that','area has a pest infestation that','area has a roach infestation that'), //'Pest Control',
            array('area has a wild goose that','area has a stray dog that','area has a wild animal that'), //'Wild Animal removal',
            array('bedroom window','bedroom windows','living room window','living room windows','guest room window','guest room windows','window','windows','window','windows'), //'Window Cleaning'
        );

        $this->fix_synonyms = array('fixing','repairs','repairing','restoring','mending','servicing');
        $this->install_synonyms = array('installing','installation','setting up');
        $this->create_synonyms = array('assembling','assembly','construction','creation','building','making');
        $this->remodel_synonyms = array('redeveloping','revamping','refurbishing','renovating','reconstruction','rebuilding','remodelling','restoring','improvement','improving');
        $this->garden_synonyms = array('landscaping','restoring','improvement','improving','weeding','trimming','fixing');
        $this->organizing_room_synonyms = array('cleaning','organizing','improving');
        $this->painting_synonyms = array('painting','spray-painting','painting','airbrushing','painting');
        
        $this->actionArr = array( // array()
            array('fixing','installation','repairs','installing','repairing','restoring','mending','servicing'), // 'General Plumbing Project'
            $this->create_synonyms, // 'General Carpentry Project',
            $this->garden_synonyms, // 'General Gardening Project',
            array('installing','installation','setting up','fixing','repairs','repairing','restoring','mending','servicing'), // 'General Electrical Project',
            $this->remodel_synonyms, // 'General Home Improvement Project',
            $this->organizing_room_synonyms, // 'General Cleaning Project',
            $this->install_synonyms, // 'Bathroom Sink Installation',
            $this->fix_synonyms, // 'Bathroom Sink Repair',
            $this->fix_synonyms, // 'Bathtub Repair',
            $this->install_synonyms, // 'Drainage Installation',
            $this->fix_synonyms, // 'Drainage Repair',
            $this->install_synonyms, // 'General Sink Installation',
            $this->fix_synonyms, // 'General Sink Repair',
            $this->install_synonyms, // 'Kitchen Sink Installation',
            $this->fix_synonyms, // 'Kitchen Sink Repair',
            $this->install_synonyms, // 'Pipe Installation',
            $this->fix_synonyms, // 'Pipe Repair',
            $this->fix_synonyms, // 'Shower Repair',
            $this->install_synonyms, //array(), // 'Toilet Installation',
            $this->fix_synonyms, //'Toilet Repair',
            $this->fix_synonyms, //'Antique Furniture Restoration',
            array('paving','surface paving'), //'Asphalt Paving Service', 
            $this->remodel_synonyms, //'Balcony Remodelling',
            $this->create_synonyms, //'Cabinet Assembly',
            $this->create_synonyms, //'Cabinet Construction',
            $this->create_synonyms, //'Cabinet Making',
            $this->fix_synonyms, //'Cabinet Repair',
            array('caulking','sealing'), //'Caulking',
            $this->create_synonyms, //'Closet Construction',
            $this->remodel_synonyms, //'Closet Renovation',
            $this->install_synonyms, //'Countertop Installation',
            $this->fix_synonyms, //'Countertop Repair',
            $this->install_synonyms, //'Door Installation',
            $this->fix_synonyms, //'Door Repair',
            $this->install_synonyms, //'Fence Installation',
            $this->fix_synonyms, //'Fence Repair',
            $this->install_synonyms, //'Foam Roofing Installation',
            $this->fix_synonyms, //'Foam Roofing Repair',
            $this->create_synonyms, //'Furniture Assembly',
            $this->fix_synonyms, //'Furniture Repair',
            $this->fix_synonyms, //'Furniture Restoration',
            $this->install_synonyms, //'Garage Door Installation',
            $this->fix_synonyms, //'Garage Door Repair',
            $this->install_synonyms, //'Gate Installation',
            $this->fix_synonyms, //'Gate Repair',
            $this->fix_synonyms, //'General Home Repair Service',
            $this->remodel_synonyms, //'Home Remodelling',
            $this->install_synonyms, //'Insulation Installation',
            $this->fix_synonyms, //'Insulation Repair',
            array('opening','unlocking','unlatching','unfastening'), //'Locksmith Service',
            $this->create_synonyms, //'Roof Construction',
            $this->fix_synonyms, //'Roof Repair',
            $this->create_synonyms, //'Shelf Assembly',
            $this->create_synonyms, //'Shelf Construction',
            $this->install_synonyms, //'Shelf Installation',
            $this->install_synonyms, //'Tile Installation',
            $this->fix_synonyms, //'Tile Repair',
            $this->install_synonyms, //'Vinyl Flooring Installation',
            $this->fix_synonyms, //'Vinyl Flooring Repair',
            array('welding','repairing','fusing'), //'Welding Services',
            $this->install_synonyms, //'Wood Flooring Installation',
            $this->fix_synonyms, //'Wood Flooring Repair',
            $this->install_synonyms, //'(Aircon) Air Conditioner Installation',
            array('maintaining','maintainance'), //'(Aircon) Air Conditioner Maintenance',
            $this->fix_synonyms, //'(Fridge) Refridgerator Repair',
            $this->fix_synonyms, //'(TV) Television Repair',
            array('mounting','installing','setting up'), //'(TV) Television Mounting',
            $this->install_synonyms, //'Alarm System Installation',
            $this->fix_synonyms, //'Appliance Repair',
            $this->create_synonyms, //'Backup Generator Installation',
            $this->install_synonyms, //'Cable and Wire Installation',
            $this->fix_synonyms, //'Cable and Wire Repair',
            $this->fix_synonyms, //'Cellphone Repair',
            $this->fix_synonyms, //'Smartphone Repair',
            $this->install_synonyms, //'Electrical Lighting Installation',
            $this->fix_synonyms, //'Electrical Lighting Repair',
            $this->fix_synonyms, //'Gadget Repair',
            $this->fix_synonyms, //'General Gadget/Appliance Repair Service',
            $this->install_synonyms, //'Generator Installation',
            $this->fix_synonyms, //'Generator Repair',
            $this->fix_synonyms, //'Laptop/Computer Repair',
            $this->fix_synonyms, //'Microwave Repair',
            $this->create_synonyms, //'Solar Panel Mounting',
            $this->fix_synonyms, //'Solar Panel Repair',
            $this->install_synonyms, //'Wiring Installation',
            $this->fix_synonyms, //'Wiring Repair',
            array('trimming','decorating','clipping','oranamentation','edging'), //'Bush/Hedge Decoration',
            $this->garden_synonyms, //'Clearing Garden',
            $this->garden_synonyms, //'Garden Landscaping',
            $this->garden_synonyms, //'Grass/Lawn Mowing',
            $this->garden_synonyms, //'Grounds Maintenance',
            array('trimming','clipping','cutting','edging'), //'Hedge Trimming',
            $this->garden_synonyms, //'Lawn Care Service',
            array('trimming','clipping','cutting','edging','cutting down','chopping'), //'Tree Service',
            $this->garden_synonyms, //'Weeding Garden',
            array('decorating','designing','remodelling','improving','refurbishing','enhacing','accessorizing'), //'Aparment/Home Decoratoring',
            array('decorating','designing','remodelling','improving','refurbishing','enhacing','accessorizing'), //'Apartment/Home Interior Designer',
            $this->painting_synonyms, //'Decorative Painters',
            $this->painting_synonyms, //'Exterior Painting',
            $this->painting_synonyms, //'Mural Painting',
            $this->painting_synonyms, //'General Home Painting',
            $this->create_synonyms, //'(Aircon) Air Conditioner Cleaning',
            $this->organizing_room_synonyms, //'Air Duct Cleaning',
            $this->organizing_room_synonyms, //'Apartment Moving Out Cleaning',
            array('removal','removing'), //'Basement Mould Removal',
            $this->organizing_room_synonyms, //'Bathroom Cleaning',
            array('cleaning','washing', 'steam cleaning', 'laundering'), //'Carpet Cleaning',
            array('cleaning','washing', 'steam cleaning', 'laundering'), //'Carpet Steam Cleaning',
            $this->organizing_room_synonyms, //'Cleaning and Organizing Service',
            array('cleaning','washing', 'steam cleaning', 'laundering'), //'Curtain Cleaning',
            $this->organizing_room_synonyms, //'Floor Care Service',
            $this->organizing_room_synonyms, //'Gutter Cleaning',
            array('removal','removing','control','controlling'), //'Pest Control',
            array('removal','removing','control','controlling'), //'Wild Animal removal',
            array('cleaning','washing', 'steam cleaning'), //'Window Cleaning'
        );

        $this->sdfsdf = array( // array()
            array(), // 'General Plumbing Project'
            array(), // 'General Carpentry Project',
            array(), // 'General Gardening Project',
            array(), // 'General Electrical Project',
            array(), // 'General Home Improvement Project',
            array(), // 'General Cleaning Project',
            array(), // 'Bathroom Sink Installation',
            array(), // 'Bathroom Sink Repair',
            array(), // 'Bathtub Repair',
            array(), // 'Drainage Installation',
            array(), // 'Drainage Repair',
            array(), // 'General Sink Installation',
            array(), // 'General Sink Repair',
            array(), // 'Kitchen Sink Installation',
            array(), // 'Kitchen Sink Repair',
            array(), // 'Pipe Installation',
            array(), // 'Pipe Repair',
            array(), // 'Shower Repair',
            array(), //array(), // 'Toilet Installation',
            array(), //'Toilet Repair',
            array(), //'Antique Furniture Restoration',
            array(), //'Asphalt Paving Service',
            array(), //'Balcony Remodelling',
            array(), //'Cabinet Assembly',
            array(), //'Cabinet Construction',
            array(), //'Cabinet Making',
            array(), //'Cabinet Repair',
            array(), //'Caulking',
            array(), //'Closet Construction',
            array(), //'Closet Renovation',
            array(), //'Countertop Installation',
            array(), //'Countertop Repair',
            array(), //'Door Installation',
            array(), //'Door Repair',
            array(), //'Fence Installation',
            array(), //'Fence Repair',
            array(), //'Foam Roofing Installation',
            array(), //'Foam Roofing Repair',
            array(), //'Furniture Assembly',
            array(), //'Furniture Repair',
            array(), //'Furniture Restoration',
            array(), //'Garage Door Installation',
            array(), //'Garage Door Repair',
            array(), //'Gate Installation',
            array(), //'Gate Repair',
            array(), //'General Home Repair Service',
            array(), //'Home Remodelling',
            array(), //'Insulation Installation',
            array(), //'Insulation Repair',
            array(), //'Locksmith Service',
            array(), //'Roof Construction',
            array(), //'Roof Repair',
            array(), //'Shelf Assembly',
            array(), //'Shelf Construction',
            array(), //'Shelf Installation',
            array(), //'Tile Installation',
            array(), //'Tile Repair',
            array(), //'Vinyl Flooring Installation',
            array(), //'Vinyl Flooring Repair',
            array(), //'Welding Services',
            array(), //'Wood Flooring Installation',
            array(), //'Wood Flooring Repair',
            array(), //'(Aircon) Air Conditioner Installation',
            array(), //'(Aircon) Air Conditioner Maintenance',
            array(), //'(Fridge) Refridgerator Repair',
            array(), //'(TV) Television Repair',
            array(), //'(TV) Television Mounting',
            array(), //'Alarm System Installation',
            array(), //'Appliance Repair',
            array(), //'Backup Generator Installation',
            array(), //'Cable and Wire Installation',
            array(), //'Cable and Wire Repair',
            array(), //'Cellphone Repair',
            array(), //'Smartphone Repair',
            array(), //'Electrical Lighting Installation',
            array(), //'Electrical Lighting Repair',
            array(), //'Gadget Repair',
            array(), //'General Gadget/Appliance Repair Service',
            array(), //'Generator Installation',
            array(), //'Generator Repair',
            array(), //'Laptop/Computer Repair',
            array(), //'Microwave Repair',
            array(), //'Solar Panel Mounting',
            array(), //'Solar Panel Repair',
            array(), //'Wiring Installation',
            array(), //'Wiring Repair',
            array(), //'Bush/Hedge Decoration',
            array(), //'Clearing Garden',
            array(), //'Garden Landscaping',
            array(), //'Grass/Lawn Mowing',
            array(), //'Grounds Maintenance',
            array(), //'Hedge Trimming',
            array(), //'Lawn Care Service',
            array(), //'Tree Service',
            array(), //'Weeding Garden',
            array(), //'Aparment/Home Decoratoring',
            array(), //'Apartment/Home Interior Designer',
            array(), //'Decorative Painters',
            array(), //'Exterior Painting',
            array(), //'Mural Painting',
            array(), //'General Home Painting',
            array(), //'(Aircon) Air Conditioner Cleaning',
            array(), //'Air Duct Cleaning',
            array(), //'Apartment Moving Out Cleaning',
            array(), //'Basement Mould Removal',
            array(), //'Bathroom Cleaning',
            array(), //'Carpet Cleaning',
            array(), //'Carpet Steam Cleaning',
            array(), //'Cleaning and Organizing Service',
            array(), //'Curtain Cleaning',
            array(), //'Floor Care Service',
            array(), //'Gutter Cleaning',
            array(), //'Pest Control',
            array(), //'Wild Animal removal',
            array(), //'Window Cleaning'
        );

        $this->expertise = array(
            'General Plumbing Project',
            'General Carpentry Project',
            'General Electrical Project',
            'General Gardening Project',
            'General Home Improvement Project',
            'General Cleaning Project',
            'Bathroom Sink Installation',
            'Bathroom Sink Repair',
            'Bathtub Repair',
            'Drainage Installation',
            'Drainage Repair',
            'General Sink Installation',
            'General Sink Repair',
            'Kitchen Sink Installation',
            'Kitchen Sink Repair',
            'Pipe Installation',
            'Pipe Repair',
            'Shower Repair',
            'Toilet Installation',
            'Toilet Repair',
            'Antique Furniture Restoration',
            'Asphalt Paving Service',
            'Balcony Remodelling',
            'Cabinet Assembly',
            'Cabinet Construction',
            'Cabinet Making',
            'Cabinet Repair',
            'Caulking',
            'Closet Construction',
            'Closet Renovation',
            'Countertop Installation',
            'Countertop Repair',
            'Door Installation',
            'Door Repair',
            'Fence Installation',
            'Fence Repair',
            'Foam Roofing Installation',
            'Foam Roofing Repair',
            'Furniture Assembly',
            'Furniture Repair',
            'Furniture Restoration',
            'Garage Door Installation',
            'Garage Door Repair',
            'Gate Installation',
            'Gate Repair',
            'General Home Repair Service',
            'Home Remodelling',
            'Insulation Installation',
            'Insulation Repair',
            'Locksmith Service',
            'Roof Construction',
            'Roof Repair',
            'Shelf Assembly',
            'Shelf Construction',
            'Shelf Installation',
            'Tile Installation',
            'Tile Repair',
            'Vinyl Flooring Installation',
            'Vinyl Flooring Repair',
            'Welding Services',
            'Wood Flooring Installation',
            'Wood Flooring Repair',
            '(Aircon) Air Conditioner Installation',
            '(Aircon) Air Conditioner Maintenance',
            '(Fridge) Refridgerator Repair',
            '(TV) Television Repair',
            '(TV) Television Mounting',
            'Alarm System Installation',
            'Appliance Repair',
            'Backup Generator Installation',
            'Cable and Wire Installation',
            'Cable and Wire Repair',
            'Cellphone Repair',
            'Smartphone Repair',
            'Electrical Lighting Installation',
            'Electrical Lighting Repair',
            'Gadget Repair',
            'General Gadget/Appliance Repair Service',
            'Generator Installation',
            'Generator Repair',
            'Laptop/Computer Repair',
            'Microwave Repair',
            'Solar Panel Mounting',
            'Solar Panel Repair',
            'Wiring Installation',
            'Wiring Repair',
            'Bush/Hedge Decoration',
            'Clearing Garden',
            'Garden Landscaping',
            'Grass/Lawn Mowing',
            'Grounds Maintenance',
            'Hedge Trimming',
            'Lawn Care Service',
            'Tree Service',
            'Weeding Garden',
            'Aparment/Home Decoratoring',
            'Apartment/Home Interior Designer',
            'Decorative Painters',
            'Exterior Painting',
            'Mural Painting',
            'General Home Painting',
            '(Aircon) Air Conditioner Cleaning',
            'Air Duct Cleaning',
            'Apartment Moving Out Cleaning',
            'Basement Mould Removal',
            'Bathroom Cleaning',
            'Carpet Cleaning',
            'Carpet Steam Cleaning',
            'Cleaning and Organizing Service',
            'Curtain Cleaning',
            'Floor Care Service',
            'Gutter Cleaning',
            'Pest Control',
            'Wild Animal removal',
            'Window Cleaning'
        );

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


        // $userID =  $lastWorkers[0]['user_id'];


        // // // for starts here
        for($z=0; $z< $total; $z++){

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

            $userID =  $lastWorkers[$z]['user_id'];

            // // SAVE CITIES
            $cityMinus = range(1,12);
            shuffle($cityMinus);
            //2 <= 1
            while($total_preferred_cities >= count($cities_toAdd)){
                if(count($cities_toAdd)==0){ // ensures that at least one of the cities is a set city 1-12
                    array_push($cities_toAdd, $cityMain);
                    unset( $cityMinus[array_search($cityMain, $cityMinus)]);
                    $cityMinus = array_values($cityMinus);
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
                    $skills_list_minus = array_values($skills_list_minus);
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


        // // Update support ticket date
        $creationDate = $this->generateData->getUserCreationDate($userID);
        $creationDate = $creationDate['data']['created_on'];
        $updateTicket = $this->generateData->updateSupportTicketDate($userID, $creationDate);

        // // Update ticket actions date
        $updateTicketAction = $this->generateData->updateTicketActionsDate($userID, $creationDate);
        // // //  for ends here

        // // Update nbi date
        $nbiID =  $this->generateData->updateNBIDate($userID, $creationDate);
        
        }





        $resData = [];
        // $resData["lastWorkers"] = $lastWorkers;
        $resData["userID"] = $userID;
        $resData["creationDate"] = $creationDate;
        // $resData["nbiID"] = $nbiID;
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
                $date = $this->random_dates("2020-01-01 08:00:00", "2022-03-01 9:00:00");
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














// ============================================
// ============================================
// June 11, 2022

public function approveWorker(Request $request,Response $response, array $args){
    $total = CustomRequestHandler::getParam($request,"total");

    $startID = CustomRequestHandler::getParam($request,"start");
    $startID = $startID == null ? 60 : $startID;

    // get list of support tickets after id 59 (60 above) that are new
    $newRegistrations = $this->generateData->getnewRegistrationTickets($total, $startID);
    $newRegistrations =  $newRegistrations['data'];




    // // // old code comment out (for ticket action date fix)
    // for($x=0;$x<count($newRegistrations);$x++){
    //    // get the ID & creation date of the support ticket
    //    $ticketID =  $newRegistrations[$x]['id'];
    //    $ticketCreationDate =  $newRegistrations[$x]['created_on'];
   
    //    $authorID =  $newRegistrations[$x]['author'];
    //    $updateTicketAction = $this->generateData->updateTicketActionsDate($authorID, $ticketCreationDate);
    // }
 





for($x=0;$x<count($newRegistrations);$x++){

    // get the ID & creation date of the support ticket
    $ticketID =  $newRegistrations[$x]['id'];
    $ticketCreationDate =  $newRegistrations[$x]['created_on'];
    $authorID =  $newRegistrations[$x]['author'];


    // $ticketCreationDate =  "2020-01-05 17:30:19"; // Debug Only Comment Out

    // get a list of support agents who have been created before the creation of the support ticket
    $qualifiedAgents = $this->generateData->getAgetnListBasedOnTicketCreation($ticketCreationDate, 1);
    $qualifiedAgents =  $qualifiedAgents['data'];
    shuffle($qualifiedAgents);

    // get a random agent to assign the ticket to
    $assignedAgentIndex = mt_rand(0,count($qualifiedAgents)-1);
    $assignedAgent =   $qualifiedAgents[$assignedAgentIndex];
    $assignedAgentID = $assignedAgent['id'];
    // get a random date from time of the creation of the support ticket until 2 days after
    $fastNess = mt_rand(0,100);
    $days = '1';
    if($fastNess % 5 == 0){
        $days = '2';
    }
    $dateticketassigned = $this->random_dates($ticketCreationDate, date('Y-m-d H:i:s', strtotime($ticketCreationDate. ' + '.$days.' days')));

    // Assign ticket to agent
    $assignAgent = $this->generateData->assign_ticket($dateticketassigned,$assignedAgentID,$ticketID,2,"AGENT #".$assignedAgentID." ACCEPTED TICKET",2);

    // Add Agent comments to the ticket
    $comment1Arr = array(
        "NBI was verified online and information submitted matched.",
        "Cx gave correct info",
        "Verified online",
        "Info verified & matches submission",
        "NBI info correct & verified",
        "Information matches & NBI verified online",
        "Correct NBI information & verified on NBI website",
        "NBI website verified & information true"
    );
    $asgnAgntComment1 = $comment1Arr[mt_rand(0,count($comment1Arr)-1)];
    $newDate = date('Y-m-d H:i:s', strtotime($ticketCreationDate. ' + '.mt_rand(3,12).' minutes'));
    $newDate = $this->random_dates( $newDate, date('Y-m-d H:i:s', strtotime( $newDate. ' + '.mt_rand(3,25).' minutes')));
    $commentRes = $this->generateData->commentTicket($newDate, $ticketID,   $assignedAgentID,   $asgnAgntComment1);

// // // For Debug Values
//     $newDate = "2020-01-05 17:43:37";
//     $assignedAgent =   array(
//         "id"=> "416",
//         "email"=> "rblackburn@support.com",
//         "role_type"=> "1",
//         "supervisor_id"=> "395",
//         "is_deleted"=> "0",
//         "created_on"=> "2020-01-01 08:00:00",
//         "user_id"=> "416",
//         "user_type_id"=> "3",
//         "user_status_id"=> "2",
//         "first_name"=> "Ralph",
//         "last_name"=> "Blackburn",
//         "phone_no"=> "09271542347",
//         "password"=> "$2y$10$.jadlIJl4zr6D61QR2v1/O1Oy2O3B6UBZlamuA4eLVE3BYUhOWZAS",
//         "messenger_status_id"=> "1",
//         "timestamp_last_active"=> "2022-06-11 05:54:37"
//     );
//     $assignedAgentID = 416;
//     $ticketID =  88;
//     $didCustomerPickUp = false;
// // // For Debug Values


$otherAgentCxNotesArr = array(
    "Applicant called to inquire about status. Informed applicant that agent is still processing application.",
    "Candidate called to check application status. Told Candidate a rep will call soon.",
    "Cx called for status. Informed Cx rep wil call in 24 hours.",
    "Applicant called in and informed to wait for processing.",
    "Candidate inquired about application. Told them rep will call within the day."
 );


 $agentReachedCxNotesArr = array(
    "Applicant was able to verify identity through live call",
    "Live call was conducted and candidate's identity is verified",
    "Sucessfully called Cx and verified Identity",
    "Was able to reach Candidate and verify identity through live call",
    "Called candidate and verified application"
);

// $customerPickedUp = 12; // For Debug Only

    // Agent called and was able to get a hold of Cx
    $newDate = $this->random_dates( $newDate, date('Y-m-d H:i:s', strtotime( $newDate. ' + '.mt_rand(5,25).' minutes')));
    $customerPickedUp = mt_rand(1,100);
    $didCustomerPickUp = !($customerPickedUp%2==0 && $customerPickedUp%3==0);
    if($didCustomerPickUp == false){ // 16% chance customer will not pick up
        $minutes = mt_rand(1,45);
        //agentCallCxTimeNotAnswered
        $newDate = date('Y-m-d H:i:s', strtotime($newDate. ' + '.$minutes.' minutes'));

        // Agent leaves notes
        $agentMessagesArr = array(
            "Tried to conduct verification but Cx did not pick up phone",
            "Called candidate for identity verification and no answer",
            "Applicant did not answer the phone for the verification call. Will try again later.",
            "No response for Identity verification. Will cagain in 24 hours.",
            "Busy tone and applicant cannot be reached. Will try again in 24 hours."
        );
        $agentUnanswerMessage = $agentMessagesArr[mt_rand(0,count($agentMessagesArr)-1)];
        $commentRes = $this->generateData->commentTicket($newDate, $ticketID,   $assignedAgentID, $agentUnanswerMessage);


        $minutes = mt_rand(1,185);
        // agentLeaveNotifTime
        $newDate = date('Y-m-d H:i:s', strtotime($newDate. ' + '.$minutes.' minutes'));
        // agent leaves Cx notification
        $agentCxNotesArr = array(
            "Please expect a call within 24 hours. Failure to answer call will result in a cancelled application.",
            "Please answer the call from a respresentative within the next 24 hours. A forfiture of application will occur in failure to answer.",
            "A representative will contact you within 24 hours. Please answer the call or application will be forfited.",
            "For identity check purposes, a representative will contact you within 24 hours. The application will be cancelled on a failure to respond to the call."
        );
        $agentCxNotes = $agentCxNotesArr[mt_rand(0,count($agentCxNotesArr)-1)];
        $commentRes = $this->generateData->commentTicket($newDate, $ticketID,   $assignedAgentID,   $agentCxNotes, 2);
    } 

    // if agent call date is greater than 1 day, then the Cx will follow up
    $hours = mt_rand(1,36);
    // $hours = mt_rand(25,36); // For Debug Only
    // $hours = mt_rand(1,24); // For Debug Only
    $willCustomerFollowUp = $hours > 24;
    if(  $willCustomerFollowUp == true){
        // Get remaining agents
        unset( $qualifiedAgents[array_search($assignedAgent, $qualifiedAgents)]);
        $qualifiedAgents = array_values($qualifiedAgents);
        // get Random Agent who will leave a message
        $otherAgent = $qualifiedAgents[ mt_rand(0,(count($qualifiedAgents)-1))];
        $otherAgentID = $otherAgent['id'];

        $hours = mt_rand(25,36);

        // cxCallAgentFollowUpTime
        $newDate = date('Y-m-d H:i:s', strtotime($newDate. ' + '.$hours.' hours'));
        // Other Agent Leaves a message
        $otherAgentCxNotes = $otherAgentCxNotesArr[mt_rand(0,count($otherAgentCxNotesArr)-1)];
        $commentRes = $this->generateData->commentTicket($newDate, $ticketID,  $otherAgentID , $otherAgentCxNotes, 1);
    
        $hours = mt_rand($hours+1,40);
    } 

    // add final closing comments
    // agentCallCxTime
    $newDate = date('Y-m-d H:i:s', strtotime($newDate. ' + '.$hours.' hours'));
    $agentReachedCxNotes = $agentReachedCxNotesArr[mt_rand(0,count($agentReachedCxNotesArr)-1)];
    $commentRes = $this->generateData->commentTicket($newDate, $ticketID,  $assignedAgentID, $agentReachedCxNotes);

    $newDate = date('Y-m-d H:i:s', strtotime($newDate. ' + '.mt_rand(0,23).' minutes'));

    $nbiID =  $this->generateData->getNBIInfo($ticketID);
    $nbiID =   $nbiID['data']['id'];

    $randy=mt_rand(0,100);
    $comment = ($randy%3==0 || $randy%2==0) ? null : "Verified docs & identity";
    // approve ticket
    $approveRes = $this->generateData->update_worker_registration($newDate, $ticketCreationDate, $assignedAgentID, $ticketID, $authorID, $nbiID, 1,  $comment);

    

}



    $resData = [];
//     // $resData['newRegistrations'] = $newRegistrations;
    $resData['ticketID'] = $ticketID;
    // $resData['authorID'] = $authorID;
    // $resData['updateTicketAction'] = $updateTicketAction;
    $resData['assignedAgentID'] = $assignedAgentID;
//     $resData['newDate'] = $newDate;
    // $resData['didCustomerPickUp'] = $didCustomerPickUp;

//     // $resData['agentCallCxTimeNotAnswered'] = $agentCallCxTimeNotAnswered;
    // $resData['willCustomerFollowUp'] = $willCustomerFollowUp;
//     // $resData['commentRes'] = $commentRes ;
//     // $resData['otherAgentID'] = $otherAgentID;
//     // $resData['otherAgentCxNotes'] = $otherAgentCxNotes;

//     // $resData['ticketCreationDate'] = $ticketCreationDate;
//     // $resData['assignedAgent'] = $assignedAgent;
//     // $resData['qualifiedAgents'] = $qualifiedAgents;
//     // $resData['dateticketassigned'] = $dateticketassigned;
//     // $resData['assignAgent'] = $assignAgent['data'];
    return $this->customResponse->is200Response($response,$resData);
}






public function generateJobPosts(Request $request,Response $response, array $args){
    date_default_timezone_set('Asia/Singapore');

    $include_users_date = CustomRequestHandler::getParam($request,"include_users_date");
    $include_users_date = $include_users_date == null ? '2020-01-01 08:00:00' : $include_users_date;

    /* incl_usr_dir - direction
        1 - 'backward'
        2 - 'forward'
    */
    $incl_usr_dir = CustomRequestHandler::getParam($request,"incl_usr_dir");
    $incl_usr_dir = $incl_usr_dir == null ? 2 : $incl_usr_dir;

    // Get list of homeonwers who were created before startDate
    $homeownersList = $this->generateData->getListHomeownersByCreationDate( $include_users_date, $incl_usr_dir);
    $homeownersList = $homeownersList['data'];

    $ongoing = CustomRequestHandler::getParam($request,"ongoing");
    $ongoing =  $ongoing == null ? false : $ongoing;

    // ========================

    // Get post date max
    $post_date_max = CustomRequestHandler::getParam($request,"post_date_max");
    $daysPast = CustomRequestHandler::getParam($request,"daysPast");
    $daysPast = $daysPast == null ? 12 : $daysPast;

    $post_date_max =  $post_date_max == null 
        ? ($daysPast != null 
            ? date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + '.$daysPast.' day')) 
            : date('Y-m-d H:i:s') 
          ) 
        :  $post_date_max;


    // if ongoing post date min has to be now // completed
    if($ongoing == true){
        $post_date_min = date('Y-m-d H:i:s');
        $post_date_max =  date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + '.$daysPast.' day'));
    }

    // $total = CustomRequestHandler::getParam($request,"total");
    // $total = $total == null ? 10 : $total;

    $maxEntriesNum = CustomRequestHandler::getParam($request,"maxEntriesNum");
    $maxEntriesNum =   $maxEntriesNum == null ? 5 :  $maxEntriesNum;

    $minEntriesNum = CustomRequestHandler::getParam($request,"minEntriesNum");
    $minEntriesNum =  $minEntriesNum == null ? 1 : $minEntriesNum;

    if( $minEntriesNum > $maxEntriesNum){ // Ensures u dont screw up and max is always greater than min
        $temp = $minEntriesNum;
        $minEntriesNum = $maxEntriesNum;
        $maxEntriesNum = $temp;
    }

    // Get expertise list
    $expertiseList = $this->file->searchProject("test");
    $expertiseList = $expertiseList['data'];

    $users = [];; // for debug 

    // -------
    // for starts here
for($xp=0; $xp< count($homeownersList); $xp++){

    // $indexUsed = $randomize == true ? mt_rand(0,count($homeownersList)-1) : $xp;

    // Make in Separate function instead
    // Specific User/Worker - if type = 2 then specific ID for worker but random for homewoner(homeowner list pulled must be specific jobs)


    // $homeowner = $homeownersList[0]; //for debug
    $homeowner = $homeownersList[$xp];


    $h_userID = $homeowner['user_id'];
    $h_cdate = $homeowner['created_on'];

    // post_date_min would be the date the user was created or the inc user data
    // whichever is greater (cannot post a post before homeowner was created) 
    if($ongoing != true){
        $post_date_min =  $h_cdate >= $include_users_date ?  $h_cdate : $include_users_date; 
    }   

    // get the user's list of homes
    $allAddress = $this->file->getUsersSavedAddresses($h_userID);
    $allAddress = $allAddress['data'];

    // array_push($users,$homeowner); // for debug
    $entriesPerPerson = mt_rand( $minEntriesNum, $maxEntriesNum);


    if(count($allAddress)>0){

// ---------------------
// second for loop starts here
        for($en = 0; $en <   $entriesPerPerson; $en++){

            // get a random expertise
            $expertiseIndex = mt_rand(0, count($expertiseList)-1);
            $expertise = $expertiseList[$expertiseIndex]["type"];
            $expertiseID = $expertiseList[$expertiseIndex]["id"];

            $ex_first_letter = strtolower(substr($expertise, 0, 1));
            $article = in_array($ex_first_letter, $this->vowels)?"an":"a";

            $object_arr_selected = count($this->objectArr) >  $expertiseIndex ? $this->objectArr[$expertiseIndex] : "";
            $obj = count($this->objectArr) >  $expertiseIndex ? $object_arr_selected[mt_rand(0, count($object_arr_selected)-1)] : "thing";

            $action_arr_selected = count($this->actionArr) >  $expertiseIndex ? $this->actionArr[$expertiseIndex] : "";
            $action = count($this->actionArr) >  $expertiseIndex ? $action_arr_selected[mt_rand(0, count($action_arr_selected)-1)] : ($this->actionArr[0])[mt_rand(0, count(($this->actionArr[0]))-1)];

            $removal_indexes = array(113,112,104);
            $pestActions = array("infests","invades","takes over","destroys","damages",'destroys','breaks','accidentally destroys','messes up',
            'dirties','damages','wrecks','breaks up','ruins');
            $pestObjArr = $this->objectArr[mt_rand(0,20)];
            $pest_control = "the ".$obj." ".($pestActions[mt_rand(0,count($pestActions)-1)]).' the '.$pestObjArr[mt_rand(0,count($pestObjArr)-1)];
            $other_situations = ($this->noun1[mt_rand(0,count($this->noun1)-1)])." ".($this->negative_action[mt_rand(0,count($this->negative_action)-1)])." my ".$obj;
            $segment = in_array($expertiseIndex,  $removal_indexes) ? $pest_control : $other_situations;

            $opening1 = "I ".($this->need_synonyms[mt_rand(0, count($this->need_synonyms)-1)])." ".($this->help_synonyms_noun[mt_rand(0, count($this->help_synonyms_noun)-1)])." with ".$article." ".strtolower($expertise).".";
            $opening2 = (mt_rand(1,100)%2==0?"Can":"Will")." someone ".($this->help_synonyms_verb[mt_rand(0, count($this->help_synonyms_verb)-1)])." with ".strtolower($expertise)."?";
            $opening3 = "This ".($this->job_syn[mt_rand(0,count($this->job_syn)-1)])." is about ".strtolower($expertise).".";

            $opening = [$opening1, $opening2, $opening3];
            shuffle($opening);

            $job_description =  $opening[mt_rand(0,count($opening)-1)]." My ".$obj." needs ".$action.". ".($this->time_period[mt_rand(0,count($this->time_period)-1)])." ".$segment." so I feel very "
                .($this->negative_feeling[mt_rand(0,count($this->negative_action)-1)])." about it. "
                .($this->irrelevent_action[mt_rand(0,count($this->irrelevent_action)-1)])." hence ".
                ($this->worker_action[mt_rand(0,count($this->worker_action)-1)]).". I would be "
                .($this->positive_feeling[mt_rand(0,count($this->positive_feeling)-1)])." if someone can ".$this->help_synonyms_verb[mt_rand(0,count($this->help_synonyms_verb)-1)]." me with this.";

            $urNum =mt_rand(1,100);
            $job_title = ($urNum%2==0 || $urNum%3==0) ? $expertise : 
            (mt_rand(1,100)%2==0 ? ucfirst($obj)." ".($this->need_synonyms_obj[mt_rand(0, count($this->need_synonyms_obj)-1)])." ".$action 
            : ( mt_rand(1,100)%2==0 ?
                ucfirst(($this->need_synonyms[mt_rand(0, count($this->need_synonyms)-1)]))." ".$expertise
                : ucfirst($this->worker_syn[mt_rand(0,count($this->worker_syn)-1)])." ".($this->need_synonyms_past[mt_rand(0,count($this->need_synonyms_past)-1)])." for ".$expertise
                )
            );

            $default_rate_type = mt_rand(1,100); 
            if($default_rate_type % 2 == 0 || $default_rate_type % 7 == 0){
                $rindex = 2;
            } else if ($default_rate_type % 3 == 0 ){
                $rindex = mt_rand(1,2); // higher probability for rt to be daily
            } else {
                $rindex = mt_rand(1,4); // less probability for rt to be project based
            }

            // random rate offer
            $rates = array(
                array(30,35,40,45,50,55,60,65,70,75,80,30,35,40,45,50,55,60,65,70,75,80,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100,105,110,115,120,125,130,135,140,145,150,155), // hour
                array(300,350,400,450,500,550,200,250,300,200,250,300,350,400,450,500,550,200,250,300,350,400,450,500,550,600,650,700,750,800), // day
                array(1300,1350,1400,1450,1500,1250,1300,1250,1300,1350,1400,1450,1500,1250,1300,1350,1400,1450,1500,1550,1600,1650,1600,1750,1700,1850,1800,1950,2000), // week
                array(1700,1850,1800,1650,1600,1750,1700,1850,1800,1950,2000,1650,1600,1750,1700,1850,1800,1950,2000,2500,2750,3000,3250,3500,3600,3650,3700,3750,3800,3950,4000,4500), // project
            );

            $chosen_rates_arr = $rates[$rindex-1];
            shuffle($chosen_rates_arr);
            $rate_offer = $chosen_rates_arr[mt_rand(0,count($chosen_rates_arr)-1)];
            $rate_type_id =  $rindex;

            $rate_thresh = array(
                90,
                600,
                1500,
                2500
            );

            if($rate_offer > $rate_thresh[$rindex-1]){
                $job_description = $job_description." I am ".($this->willling_syn[mt_rand(0,count($this->willling_syn)-1)])." to pay extra to get it "
                .($this->perform_job_syn[mt_rand(0,count($this->perform_job_syn)-1)])." ".($this->quicktime_syn2[mt_rand(0,count($this->quicktime_syn2)-1)]).".";
            }

            // Get random post date
            // user's create date until now
            $post_createDate = $this->random_dates($post_date_min);

            $home = $allAddress[0];
            // Get random home ID
            // if more than one home
            if(count($allAddress) != 0){
                $home = $allAddress[mt_rand(0,count($allAddress)-1)];
            }
            $homeID = $home['home_id'];

            // Get random job size
            $rand_job_size = mt_rand(1,3);

            // Get random preferred date
            // user's post date + 2 until post_date max
            $preferred_date = $this->random_dates( date('Y-m-d H:i:s', strtotime( $post_createDate. ' + '.mt_rand(12,42).' hours')), $post_date_max);

// ===================================================
// ===================================================
// Create a job post
                $jobPostCreation = $this->generateData->saveProject(
                    $post_createDate,
                    $h_userID,
                    $homeID, 
                    $rand_job_size, 
                    $expertiseID,
                    $job_description, 
                    $rate_offer,   
                    1,
                    $rate_type_id, 
                    $preferred_date, 
                    $job_title
                );

                // $job_post_id = $jobPostCreation['data'];


        }

    }
    // ---------------------
    // second for loop ends here


    // // -------
    // // for loop ends here
}



    $resData = [];
    // $resData['include_users_date'] = ($incl_usr_dir==1?"before ":"after ").$include_users_date;
    // $resData['post_date_max'] = $post_date_max;
    // $resData['post_date_min'] = $post_date_min;

    $resData['h_userID'] = $h_userID;
    // $resData['h_cdate'] = $h_cdate;
    // $resData['rate_offer'] = $rate_offer;
    // $resData['rate_type_id'] = $rate_type_id;
    // $resData['job_title'] = $job_title;
    // $resData['homeID'] = $homeID;
    // $resData['expertiseID'] = $expertiseID;
    // $resData['created_on'] = $post_createDate;
    // $resData['preferred_date'] = $preferred_date;
    // $resData['job_description'] = $job_description;

    // $resData['job_post_id'] =   $job_post_id;

    // $resData['users'] = $users;

    // $resData['allAddress'] = $allAddress;
    // $resData['expertiseList'] = $expertiseList;
    // $resData['homeownersList'] =  $homeownersList;
    // $resData['home'] = $home;

    return $this->customResponse->is200Response($response,$resData);
}









// ===============================================
// ===============================================
// ===============================================
//          June 13, 2022
// ===============================================
// ===============================================
// ===============================================
public function generateJobOrder(Request $request,Response $response, array $args){
    date_default_timezone_set('Asia/Singapore');

    $include_users_date = CustomRequestHandler::getParam($request,"include_users_date");
    $include_users_date = $include_users_date == null ? '2020-01-01 08:00:00' : $include_users_date;

    /* incl_usr_dir - direction
        1 - 'backward'
        2 - 'forward'
    */
    $incl_usr_dir = CustomRequestHandler::getParam($request,"incl_usr_dir");
    $incl_usr_dir = $incl_usr_dir == null ? 2 : $incl_usr_dir;


    // Get list of homeonwers who were created before startDate
    $homeownersList = $this->generateData->getListHomeownersByCreationDate( $include_users_date, $incl_usr_dir);
    $homeownersList = $homeownersList['data'];



    // ========================
    // GET DATE SETTINGS

    /* completion stage
        1 - accepted
        2 - started
        3 - completed
    */
    $completion_stage = CustomRequestHandler::getParam($request,"completion_stage");
    $completion_stage =  $completion_stage == null ? 3 : $completion_stage;

    $ongoing = CustomRequestHandler::getParam($request,"ongoing");
    $ongoing =  $ongoing == null ? false : $ongoing;

    // Get post date max
    $post_date_max = CustomRequestHandler::getParam($request,"post_date_max");
    $daysPast = CustomRequestHandler::getParam($request,"daysPast");
    $daysPast = $daysPast == null ? 12 : $daysPast;

    $post_date_max =  $post_date_max == null 
        ? ($daysPast != null 
            ? date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + '.$daysPast.' day')) 
            : date('Y-m-d H:i:s') 
          ) 
        :  $post_date_max;

    // if($completion_stage >= 2){
    //     $post_date_max =
    // }


    // if ongoing post date min has to be now // completed
    if($ongoing == true){
        $post_date_min = date('Y-m-d H:i:s');
        $post_date_max =  date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' + '.$daysPast.' day'));
    }

    // $total = CustomRequestHandler::getParam($request,"total");
    // $total = $total == null ? 10 : $total;

    $maxEntriesNum = CustomRequestHandler::getParam($request,"maxEntriesNum");
    $maxEntriesNum =   $maxEntriesNum == null ? 5 :  $maxEntriesNum;

    $minEntriesNum = CustomRequestHandler::getParam($request,"minEntriesNum");
    $minEntriesNum =  $minEntriesNum == null ? 1 : $minEntriesNum;

    if( $minEntriesNum > $maxEntriesNum){ // Ensures u dont screw up and max is always greater than min
        $temp = $minEntriesNum;
        $minEntriesNum = $maxEntriesNum;
        $maxEntriesNum = $temp;
    }

    // Get expertise list
    $expertiseList = $this->file->searchProject("test");
    $expertiseList = $expertiseList['data'];

    $users = [];; // for debug 

    // -------
    // for starts here
for($xp=0; $xp< count($homeownersList); $xp++){

    // $indexUsed = $randomize == true ? mt_rand(0,count($homeownersList)-1) : $xp;

    // Make in Separate function instead
    // Specific User/Worker - if type = 2 then specific ID for worker but random for homewoner(homeowner list pulled must be specific jobs)


    // $homeowner = $homeownersList[0]; //for debug
    // $homeowner = $homeownersList[1]; //for debug has 2 different cities
    $homeowner = $homeownersList[$xp];



    $h_userID = $homeowner['user_id'];
    $h_cdate = $homeowner['created_on'];

    // post_date_min would be the date the user was created or the inc user data
    // whichever is greater (cannot post a post before homeowner was created) 
    if($ongoing != true){
        $post_date_min =  $h_cdate >= $include_users_date ?  $h_cdate : $include_users_date; 
    } 

    // get the user's list of homes
    $allAddress = $this->file->getUsersSavedAddresses($h_userID);
    $allAddress = $allAddress['data'];

    // array_push($users,$homeowner); // for debug
    $entriesPerPerson = mt_rand( $minEntriesNum, $maxEntriesNum);


    if(count($allAddress)>0){

// ---------------------
// second for loop starts here
        for($en = 0; $en <   $entriesPerPerson; $en++){

            // get a random expertise
            $expertiseIndex = mt_rand(0, count($expertiseList)-1);
            $expertise = $expertiseList[$expertiseIndex]["type"];
            $expertiseID = $expertiseList[$expertiseIndex]["id"];
            $baseExpertise = $expertiseList[$expertiseIndex]['expertise'];

            $ex_first_letter = strtolower(substr($expertise, 0, 1));
            $article = in_array($ex_first_letter, $this->vowels)?"an":"a";

            $object_arr_selected = count($this->objectArr) >  $expertiseIndex ? $this->objectArr[$expertiseIndex] : "";
            $obj = count($this->objectArr) >  $expertiseIndex ? $object_arr_selected[mt_rand(0, count($object_arr_selected)-1)] : "thing";

            $action_arr_selected = count($this->actionArr) >  $expertiseIndex ? $this->actionArr[$expertiseIndex] : "";
            $action = count($this->actionArr) >  $expertiseIndex ? $action_arr_selected[mt_rand(0, count($action_arr_selected)-1)] : ($this->actionArr[0])[mt_rand(0, count(($this->actionArr[0]))-1)];

            $removal_indexes = array(113,112,104);
            $pestActions = array("infests","invades","takes over","destroys","damages",'destroys','breaks','accidentally destroys','messes up',
            'dirties','damages','wrecks','breaks up','ruins');
            $pestObjArr = $this->objectArr[mt_rand(0,20)];
            $pest_control = "the ".$obj." ".($pestActions[mt_rand(0,count($pestActions)-1)]).' the '.$pestObjArr[mt_rand(0,count($pestObjArr)-1)];
            $other_situations = ($this->noun1[mt_rand(0,count($this->noun1)-1)])." ".($this->negative_action[mt_rand(0,count($this->negative_action)-1)])." my ".$obj;
            $segment = in_array($expertiseIndex,  $removal_indexes) ? $pest_control : $other_situations;

            $opening1 = "I ".($this->need_synonyms[mt_rand(0, count($this->need_synonyms)-1)])." ".($this->help_synonyms_noun[mt_rand(0, count($this->help_synonyms_noun)-1)])." with ".$article." ".strtolower($expertise).".";
            $opening2 = (mt_rand(1,100)%2==0?"Can":"Will")." someone ".($this->help_synonyms_verb[mt_rand(0, count($this->help_synonyms_verb)-1)])." with ".strtolower($expertise)."?";
            $opening3 = "This ".($this->job_syn[mt_rand(0,count($this->job_syn)-1)])." is about ".strtolower($expertise).".";

            $opening = [$opening1, $opening2, $opening3];
            shuffle($opening);

            $job_description =  $opening[mt_rand(0,count($opening)-1)]." My ".$obj." needs ".$action.". ".($this->time_period[mt_rand(0,count($this->time_period)-1)])." ".$segment." so I feel very "
                .($this->negative_feeling[mt_rand(0,count($this->negative_action)-1)])." about it. "
                .($this->irrelevent_action[mt_rand(0,count($this->irrelevent_action)-1)])." hence ".
                ($this->worker_action[mt_rand(0,count($this->worker_action)-1)]).". I would be "
                .($this->positive_feeling[mt_rand(0,count($this->positive_feeling)-1)])." if someone can ".$this->help_synonyms_verb[mt_rand(0,count($this->help_synonyms_verb)-1)]." me with this.";

            $urNum =mt_rand(1,100);
            $job_title = ($urNum%2==0 || $urNum%3==0) ? $expertise : 
            (mt_rand(1,100)%2==0 ? ucfirst($obj)." ".($this->need_synonyms_obj[mt_rand(0, count($this->need_synonyms_obj)-1)])." ".$action 
            : ( mt_rand(1,100)%2==0 ?
                ucfirst(($this->need_synonyms[mt_rand(0, count($this->need_synonyms)-1)]))." ".$expertise
                : ucfirst($this->worker_syn[mt_rand(0,count($this->worker_syn)-1)])." ".($this->need_synonyms_past[mt_rand(0,count($this->need_synonyms_past)-1)])." for ".$expertise
                )
            );

            $default_rate_type = mt_rand(1,100); 
            if($default_rate_type % 2 == 0 || $default_rate_type % 7 == 0){
                $rindex = 2;
            } else if ($default_rate_type % 3 == 0 ){
                $rindex = mt_rand(1,2); // higher probability for rt to be daily
            } else {
                $rindex = mt_rand(1,4); // less probability for rt to be project based
            }

            // random rate offer
            $rates = array(
                array(30,35,40,45,50,55,60,65,70,75,80,30,35,40,45,50,55,60,65,70,75,80,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100,105,110,115,120,125,130,135,140,145,150,155), // hour
                array(300,350,400,450,500,550,200,250,300,200,250,300,350,400,450,500,550,200,250,300,350,400,450,500,550,600,650,700,750,800), // day
                array(1300,1350,1400,1450,1500,1250,1300,1250,1300,1350,1400,1450,1500,1250,1300,1350,1400,1450,1500,1550,1600,1650,1600,1750,1700,1850,1800,1950,2000), // week
                array(1700,1850,1800,1650,1600,1750,1700,1850,1800,1950,2000,1650,1600,1750,1700,1850,1800,1950,2000,2500,2750,3000,3250,3500,3600,3650,3700,3750,3800,3950,4000,4500), // project
            );

            $chosen_rates_arr = $rates[$rindex-1];
            shuffle($chosen_rates_arr);
            $rate_offer = $chosen_rates_arr[mt_rand(0,count($chosen_rates_arr)-1)];
            $rate_type_id =  $rindex;

            $rate_thresh = array(
                90,
                600,
                1500,
                2500
            );

            if($rate_offer > $rate_thresh[$rindex-1]){
                $job_description = $job_description." I am ".($this->willling_syn[mt_rand(0,count($this->willling_syn)-1)])." to pay extra to get it "
                .($this->perform_job_syn[mt_rand(0,count($this->perform_job_syn)-1)])." ".($this->quicktime_syn2[mt_rand(0,count($this->quicktime_syn2)-1)]).".";
            }

            // Get random post date
            // user's create date until now
            $post_createDate = $this->random_dates($post_date_min);
            // Cannot have a post created date 2 days before today if entry is completed (3)
            // completion stage 2+ 
            $hasElapsed = false;
            if($completion_stage >= 2){                
                // check if the post date minimum is within 2 day before now
                $now =  date('Y-m-d H:i:s');
                $checkPostDateMin = date('Y-m-d H:i:s', strtotime( $now. ' - 2 days'));
                // if it is, then reassign to before 2 days from now
                // readjust
                if( $checkPostDateMin <  $post_createDate){
                    $post_createDate = date('Y-m-d H:i:s', strtotime( $post_createDate. ' - 2 days'));
                    $hasElapsed = true;
                }
            }

            $home = $allAddress[0];
            // Get random home ID
            // if more than one home
            if(count($allAddress)!=1){
                $home = $allAddress[mt_rand(0,count($allAddress)-1)];
            }
            $homeID = $home['home_id'];
            $cityID = $home['city_id'];

            // Get random job size
            $rand_job_size = mt_rand(1,3);

            // Get random preferred date
            // user's post date + (1-7) days from that date
          
            $postToCreate_timeBetween = mt_rand(24,168);
            // Cannot have a preferred date beyond today if entry is completed
            // measure time between now and create date -> that is the amount of time you have for entry that is completed
            if($completion_stage >= 2 &&  $hasElapsed == true){  
                $postToCreate_timeBetween =  mt_rand(24,30);
            }
            $preferred_date = date('Y-m-d H:i:s', strtotime( $post_createDate. ' + '.$postToCreate_timeBetween.' hours'));
            
            


// ===================================================
// ===================================================
// Create a job post
                // Get a random worker
                // Get List of workers who are qualified for the job and are within the area
                // $workerID = 157; // temp for now
                // based on $expertiseID, cityID
                // Get the general ID from the expertise ID

                $workersList = $this->generateData->getListOfWorkers($baseExpertise, $cityID);
                $workersList = $workersList['data'];

                $worker = $workersList[mt_rand(0, count($workersList)-1)];
                // $workerID =  $worker['id'];
                 $workerID =  $worker['worker_id'];

               // // // 10 workers have constant bad reviews
                // $testy = [];
                // shuffle($workersList);
               // // for($v=0; $v<10; $v++){
               // //     shuffle($workersList);
               // //     shuffle($workersList);
               // //     shuffle($workersList);
               // //     shuffle($workersList);
               // //     $worker =  $workersList[mt_rand(0,count( $workersList)-1)];
               // //     array_push( $testy, $worker);
               // //     unset(    $workersList[array_search($worker,    $workersList)]);
               // //     $workersList = array_values(   $workersList);
               // //     shuffle($workersList);
               // // }



                // JO CREATE DATE MUST BE BETWEEN THE TIME THE POST WAS CREATED plus 4-42 minutes
                // AND THE TIME OF PREFERRED DATE (minus few minutes)
                $jo_createDate = $this->random_dates( 
                    date('Y-m-d H:i:s', strtotime( $post_createDate. ' + '.mt_rand(4,42).' minutes')), 
                    date('Y-m-d H:i:s', strtotime( $preferred_date. ' - '.mt_rand(80,120).' minutes'))
                );

                // --------------------------------------------------------
                // Start Time must be based on preferred date
                // Either worker is late, early or on time
                $punctuality_randomizer = mt_rand(1,100);
                // Higher chance of worker just being on time
                $punctuality = 1; // 1-on time, 2- early, 3 -late
                if( $punctuality_randomizer%5 == 0 && $punctuality_randomizer%2 != 0){ // 10% chance of being late
                    $punctuality = 3;
                } else if($punctuality_randomizer%2 == 0 && $punctuality_randomizer%3 == 0){ // 16% chance of being early
                    $punctuality = 2;
                }

                $startTime = $preferred_date; // default: // on time
                switch($punctuality){ 
                    case 2: // early -> random time of - 1 minute to less than 1 hour early
                        $startTime = date('Y-m-d H:i:s', strtotime( $preferred_date. ' - '.(mt_rand(1,50)).' minutes'));
                        break;
                    case 3: // late -> random time of - 1 minute to 2 hours late
                        $startTime = date('Y-m-d H:i:s', strtotime( $preferred_date. ' + '.(mt_rand(1,125)).' minutes'));
                        break;
                }

                // End time must be based on job order size ------------------------------------------
                // default is 2-4 hours after start
                $timeRendered = (mt_rand(2,4));
                $endTime =  date('Y-m-d H:i:s', strtotime(  $startTime. ' + '. $timeRendered.' hours'));
                switch($rand_job_size){
                    // small
                    case 1: 
                        $timeRendered = mt_rand(80,360);
                        $endTime =  date('Y-m-d H:i:s', strtotime(  $startTime. ' + '.  $timeRendered .' minutes'));
                        $timeRendered /= 60;
                        break;

                    // medium
                    case 2: 
                        $timeRendered = mt_rand(3,12);
                        $endTime =  date('Y-m-d H:i:s', strtotime(  $startTime. ' + '.  $timeRendered .' hours'));
                        break;

                    // big
                    case 3: 
                        $timeRendered = mt_rand(6,96);
                        $endTime =  date('Y-m-d H:i:s', strtotime(  $startTime. ' + '.  $timeRendered .' hours'));
                        $timeRendered /= 24;
                        $timeRendered *= 8;
                        break;
                }

// ===================================
                // GENERATE RANDOM BILL
                // bill creation date should be based on end time of worker
                $billCreationDate = $endTime ;
                $dateBillPaid = date('Y-m-d H:i:s', strtotime(  $billCreationDate. ' + '.(mt_rand(12,180)).' minutes'));
                
                $base_rate = $rate_offer;
                $totalPriceBilled = $rate_offer;

                // total price billed should be based on number of hours rendered
                // get it based on rate offer
                switch($rate_type_id){ 
                    case 2: // day
                        $base_rate = $rate_offer/8; // get hourly rate not 24 hours since there are 8 work hours in a day
                        break;
                    case 3: // week
                        $base_rate = $rate_offer/56;
                        break;
                }
                if($rate_type_id == 4){
                    $totalPriceBilled = $rate_offer;
                } else {
                    $totalPriceBilled = $timeRendered * $base_rate;
                    if($totalPriceBilled < $rate_offer){
                        $totalPriceBilled = $rate_offer;
                    }
                }




// ===================================
                // GENERATE RANDOM REVIEW
                


                $jobPostCreation = $this->generateData->createCompleteJobOrder(
                    $post_createDate,
                    $h_userID,
                    $homeID, 
                    $rand_job_size, 
                    $expertiseID,
                    $job_description, 
                    $rate_offer,   
                    1,
                    $rate_type_id, 
                    $preferred_date, 
                    $job_title,

                    $workerID, 
                    $jo_createDate,
                    $startTime, 
                    $endTime,

                    $dateBillPaid, 
                    $totalPriceBilled,
                    1, // isReceivedByWorker
                    $billCreationDate
                );

                // $job_post_id = $jobPostCreation['data'];


        }

    }
    // ---------------------
    // second for loop ends here


    // // -------
    // // for loop ends here
}



    $resData = [];
    // $resData['include_users_date'] = ($incl_usr_dir==1?"before ":"after ").$include_users_date;
    // $resData['post_date_max'] = $post_date_max;
    // $resData['post_date_min'] = $post_date_min;

    $resData['h_userID'] = $h_userID;
    // $resData['h_cdate'] = $h_cdate;
    // $resData['rate_offer'] = $rate_offer;
    // $resData['rate_type_id'] = $rate_type_id;
    // $resData['job_title'] = $job_title;
    // $resData['homeID'] = $homeID;
    // $resData['expertiseID'] = $expertiseID;
    // $resData['created_on'] = $post_createDate;
    // $resData['preferred_date'] = $preferred_date;
    // $resData['job_description'] = $job_description;

    // $resData['job_post_id'] =   $job_post_id;

    // $resData['users'] = $users;

    // $resData['allAddress'] = $allAddress;
    // $resData['expertiseList'] = $expertiseList;
    // $resData['homeownersList'] =  $homeownersList;
    // $resData['home'] = $home;

    // $resData['checkPostDateMin'] = $checkPostDateMin;
    // $resData['post_createDate'] = $post_createDate;
    // $resData['readjust'] = $checkPostDateMin <  $post_createDate;


    // $resData['home'] = $home;
    // $resData['home'] = $home;
    // $resData['cityID'] = $cityID;
    // $resData['worker'] = $worker;
    // $resData['workerID'] =  $workerID;
    // $resData['testy'] = $testy;
    // $resData['baseExpertise'] = $baseExpertise;
    // $resData['workersList'] = $workersList;

    return $this->customResponse->is200Response($response,$resData);
}
























































    public function test(Request $request,Response $response, array $args){
        // $resData = [];
        // $resData['anouncement'] =  $announce['data'];
    
        // $resData['myRole'] = $user_role;
        return $this->customResponse->is200Response($response,"This route works");
    }
}

