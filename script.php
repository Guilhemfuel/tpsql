<?php

try
{
	$bdd = new PDO('mysql:host=localhost:8889;charset=utf8', 'root', 'root');
}
catch (Exception $e)
{
    die('Erreur : ' . $e->getMessage());
}

$bdd->exec('DROP DATABASE shop;');
$bdd->exec('CREATE DATABASE shop;');
$bdd->exec('USE shop;');

$bdd->exec('CREATE TABLE `shop`.clients (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(250),
    signin_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

$bdd->exec('CREATE TABLE `shop`.phones (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    numero VARCHAR(10),
    client INT NOT NULL,
    CONSTRAINT fk_phones FOREIGN KEY phones(client) REFERENCES shop.clients(id)
)');

$bdd->exec('CREATE TABLE `shop`.produits (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nom VARCHAR(100),
    description VARCHAR(250),
    quantite INT(11)
)');

$bdd->exec('CREATE TABLE `shop`.commandes (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    client INT NOT NULL,
    date DATETIME NOT NULL,
    CONSTRAINT fk_client FOREIGN KEY commandes(client) REFERENCES shop.clients(id)
)');

$bdd->exec('CREATE TABLE `shop`.commande_produit (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    commande INT NOT NULL,
    produit INT NOT NULL,
    CONSTRAINT fk_cp_commande FOREIGN KEY commande_produit(commande) REFERENCES shop.commandes(id),
    CONSTRAINT fk_cp_produit FOREIGN KEY commande_produit(produit) REFERENCES shop.produits(id)
)');

$bdd->exec('CREATE TABLE `shop`.tags (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nom VARCHAR(100)
)');

$bdd->exec('CREATE TABLE `shop`.produit_tag (
    produit INT NOT NULL,
    tag INT NOT NULL,
    CONSTRAINT pk_produit_tag PRIMARY KEY (produit, tag),
    CONSTRAINT fk_pt_produit FOREIGN KEY produit_tag(produit) REFERENCES shop.produits(id),
    CONSTRAINT fk_pt_ptag FOREIGN KEY produit_tag(tag) REFERENCES shop.tags(id)
)');

$tags = array(
	array("nom" => "nature"),
	array("nom" => "electronique"),
	array("nom" => "vetements"),
	array("nom" => "menager"),
	array("nom" => "nourriture"),
);

$tags = json_encode($tags);
$tags = json_decode($tags);

$req = $bdd->prepare('INSERT INTO tags VALUES(:id, :nom)');

foreach($tags as $data)
{
	$req->execute(array(
	'id' => '',
	'nom' => $data->nom
	));
}

$produits = array(
	array("nom" => "PS4", "description" => "Console de jeu", "quantite" => 4,
		"tags" => array(1, 2)
	),
	array("nom" => "Chaise", "description" => "Objet confortable permettant de s'assoir", "quantite" => 2,
		"tags" => array(1, 4)
	),
	array("nom" => "Truelle", "description" => "Permet de couler du beton", "quantite" => 8,
		"tags" => array(5)
	),
	array("nom" => "Lampadaire", "description" => "Permet d'éclairer dans l'obscurité", "quantite" => 2,
		"tags" => array()
	),
);

$clients = array(
	array("nom" => "Canivet", "prenom" => "Guilhem", "email" => "guilhem@gmail.com",
		"phones" => array('0671172758', '0548451521', '0687484545', '0587484545')
	),
	array("nom" => "Dupuis", "prenom" => "Jean", "email" => "jean@gmail.com",
		"phones" => array('0548784554')
	),
	array("nom" => "Delas", "prenom" => "Henry", "email" => "henry@gmail.com",
		"phones" => array()
	),
);

$produits = json_encode($produits);
$produits = json_decode($produits);

$clients = json_encode($clients);
$clients = json_decode($clients);

$req = $bdd->prepare('INSERT INTO produits VALUES(:id, :nom, :description, :quantite)');
$req2 = $bdd->prepare('INSERT INTO produit_tag VALUES(:produit, :tag)');

foreach($produits as $data)
{
	$req->execute(array(
	'id' => '',
	'nom' => $data->nom,
	'description' => $data->description,
	'quantite' => $data->quantite
	));

	$id = $bdd->lastInsertId();

	foreach($data->tags as $data)
	{
		$req2->execute(array(
			'produit' => $id,
			'tag' => $data,
		));
	}
}

$req = $bdd->prepare('INSERT INTO clients (id, nom, prenom, email) VALUES(:id, :nom, :prenom, :email)');
$req2 = $bdd->prepare('INSERT INTO phones VALUES(:id, :numero, :client)');

foreach($clients as $data)
{
	$req->execute(array(
	'id' => '',
	'nom' => $data->nom,
	'prenom' => $data->prenom,
	'email' => $data->email
	));

	$id = $bdd->lastInsertId();

	foreach($data->phones as $data)
	{
		$req2->execute(array(
			'id' => '',
			'numero' => $data,
			'client' => $id,
		));
	}
}

$commandes = array(
	array("client" => 1, "date" => date("Y-m-d H:i:s"),
		"produits" => array(1, 2)
	),
	array("client" => 2, "date" => date("Y-m-d H:i:s"),
		"produits" => array(3)
	),
);

$commandes = json_encode($commandes);
$commandes = json_decode($commandes);

$req = $bdd->prepare('INSERT INTO commandes VALUES(:id, :client, :date)');
$req2 = $bdd->prepare('INSERT INTO commande_produit VALUES(:id, :commande, :produit)');

foreach($commandes as $data)
{
	$req->execute(array(
	'id' => '',
	'client' => $data->client,
	'date' => $data->date
	));

	$id = $bdd->lastInsertId();

	foreach($data->produits as $data)
	{
		$req2->execute(array(
			'id' => '',
			'commande' => $id,
			'produit' => $data,
		));
	}
}

//Rename signin_at
$bdd->exec('ALTER TABLE `shop`.clients CHANGE signin_at signup_at DATETIME DEFAULT CURRENT_TIMESTAMP');

$response = $bdd->query('SELECT produits.nom, COUNT(produit_tag.tag) as nb_tag 
	FROM produits 
	LEFT JOIN produit_tag ON produit_tag.produit = produits.id 
	GROUP BY nom
	ORDER BY nom ASC');

while ($donnees = $response->fetch())
{
	echo 'Produit : ' . $donnees['nom'] .' - Nombre de tag : '.$donnees['nb_tag']. '<br />';
}

?>