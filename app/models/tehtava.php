<?php

  class Tehtava extends BaseModel{

  	public $id, $kayttaja, $nimi, $tarkeys, $lisatieto, $luokka;

  	public function __construct($attributes){
   		parent::__construct($attributes);
      $this->validators = array('validate_nimi', 'validate_luokka');
   	}

   	public static function all(){

      $t = new TehtavaController();
      $tunnus = $t->get_user_logged_in();
      $t->check_logged_in();

   		$query = DB::connection()->prepare('SELECT * FROM Tehtava WHERE kayttaja = :kayttaja ORDER BY tarkeys DESC');

   		$query->execute(array('kayttaja' => $tunnus->tunnus));

   		$rows = $query -> fetchAll();

   		$tehtavat = array();

   		foreach($rows as $row){

      $luokka = self::etsiLuokat($row['id']);

   			$tehtavat[] = new Tehtava(array(
   				'id' => $row['id'],
   				'kayttaja' => $row['kayttaja'],
   				'nimi' => $row['nimi'],
   				'tarkeys' => $row['tarkeys'],
   				'lisatieto' => $row['lisatieto'],
          'luokka' => $luokka
   			));
   		}

   		return $tehtavat;
   	}

   	public static function find($id){
   		$query = DB::connection()->prepare('SELECT * FROM Tehtava WHERE id = :id LIMIT 1');
   		$query -> execute(array('id' => $id));
   		$row = $query -> fetch();

      $tehtava = array();

   		if($row){
   			$tehtava[] = new Tehtava(array(
   				'id' => $row['id'],
   				'kayttaja' => $row['kayttaja'],
   				'nimi' => $row['nimi'],
   				'tarkeys' => $row['tarkeys'],
   				'lisatieto' => $row['lisatieto'],
          'luokka' => self::etsiLuokat($row['id'])
   				));

   			return $tehtava;
   		}

   		return null;
   	}

    public function save(){

      $query = DB::connection()->prepare('INSERT INTO Tehtava(kayttaja, nimi, tarkeys, lisatieto) VALUES(:kayttaja, :nimi, :tarkeys, :lisatieto) RETURNING id');

      $query->execute(array('kayttaja' => $this->kayttaja, 'nimi' => $this->nimi, 'tarkeys' => $this->tarkeys, 'lisatieto' => $this->lisatieto));

      $row = $query->fetch();

      $this->id = $row['id'];
    }

    public function update(){
      $query = DB::connection()->prepare('UPDATE Tehtava SET (nimi, tarkeys, lisatieto) = (:nimi, :tarkeys, :lisatieto) WHERE id = :id');

      $query->execute(array('nimi' => $this->nimi, 'tarkeys' => $this->tarkeys, 'lisatieto' => $this->lisatieto, 'id' => $this->id));
    }

    public function validate_nimi(){
      $errors = array();
      if($this->nimi == '' || $this->nimi == null){
        $errors[] = 'Nimi ei saa olla tyhjä!';
      }
      
      return $errors;
    }

    public function validate_luokka(){
      $errors = array();
      if($this->luokka == '' || $this->luokka == null){
        $errors[] = 'Luokka ei saa olla tyhjä!';
      }
      
      return $errors;
    }


    public function poista(){
      $query = DB::connection()->prepare('DELETE FROM Luokitus WHERE tehtava=:id');

      $query->execute(array('id' => $this->id));

      $query = DB::connection()->prepare('DELETE FROM Tehtava WHERE id=:id');

      $query->execute(array('id' => $this->id));
    }
    
    public static function etsiLuokat($id){
      $query = DB::connection()->prepare('SELECT * FROM Luokitus WHERE tehtava = :tehtava');
      $query -> execute(array('tehtava' => $id));
      $row = $query -> fetch();

      return $row['luokka'];
    }

    public static function etsiTehtavat($nimi){
      /*$query = DB::connection()->prepare('SELECT * FROM Tehtava, Luokka, Luokitus WHERE Luokka.nimi = luokka AND id = tehtava AND Luokka.nimi = :nimi');
      $query -> execute(array('nimi' => $nimi));
      $rows = $query -> fetch();

      $tehtavat = array();

      foreach($rows as $row){

        $tehtavat[] = new Tehtava(array(
          'id' => $row['id'],
          'kayttaja' => $row['kayttaja'],
          'nimi' => $row['tehtava.nimi'],
          'tarkeys' => $row['tarkeys'],
          'lisatieto' => $row['lisatieto']
        ));
      }

      return $tehtavat;
      */

      $kaikki = self::all();
      $tehtavat = array();

      foreach($kaikki as $teht){

        $luokka = self::luokka($teht->id);

        if(strcmp($luokka,$nimi) == 0){
          $tehtavat[] = $teht;
        }
      }

      return $tehtavat;
    }

      public static function luokka($id){
        $query = DB::connection()->prepare('SELECT * FROM Luokitus WHERE tehtava = :tehtava LIMIT 1');
        $query -> execute(array('tehtava' => $id));
        $row = $query -> fetch();

       

        return $row['luokka'];
  
      }

  }