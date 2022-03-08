<?php

namespace Jmuffon\Dropbox;

use Exception;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Dropbox;


/**
 * Classe utilitaire permetant de se connecter à un compte Dropbox et de manipuler des fichiers/dossiers
 * d'une Dropbox utilisateur.
 *
 * @link: https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_shared_links
 *
 * Copyright: Jmuffon technology
 */
class DropboxUtil extends Dropbox
{
    protected $dropbox_key = null;
    protected $dropbox_secret = null;
    protected $dropbox_token = null;
    protected $app = null;
    protected $dropbox = null;

    public function __construct()
    {
        try {
            // Connexion à la Dropbox
            $this->dropbox_key = config('project.dropbox_key', "");
            $this->dropbox_secret = config('project.dropbox_secret', "");
            $this->dropbox_token = config('project.dropbox_token', "");

            $this->app = new DropboxApp($this->dropbox_key, $this->dropbox_secret, $this->dropbox_token);
            $this->dropbox = new Dropbox($this->app);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Permet d'uploader un fichier dans la dropbox. Le chemin complet est créé si besoin
     *
     * Retourne le nom du fichier Dropbox ou false si la fonction échoue
     */
    public function uploadFile($from, $to)
    {
        if (is_null($from) || is_null($to)) {
            throw new \Exception("Dropbox file parameters 'Source' or 'Destination' is/are null. It's impossible to upload the file!");
        }

        $file = $this->dropbox->upload($from, $to, ['autorename' => true]);
        if (is_null($file)) {
            throw new \Exception("Upload file on Dropbox failed!");
        }
        return $file->getPathLower();
    }
    /**
     * Permet de supprimer un dossier de la dropbox avec tous les fichiers à l'intérieur.
     *
     * Retourne le nom du dossier Dropbox effacé ou null si la fonction échoue
     */
    public function deleteFolder($foldername)
    {
        if (is_null($foldername)) {
            throw new \Exception("Folder name on Dropbox is null. It's impossible to delete folder!");
        }
        $deletedFolder = $this->dropbox->delete($foldername);
        if (is_null($deletedFolder)) {
            throw new \Exception("Delete folder on Dropbox failed!");
        }
        return $deletedFolder->getName();
    }
    /**
     * Permet de supprimer un fichier de la dropbox.
     *
     * Retourne le nom du fichier Dropbox effacé ou null si la fonction échoue
     */
    public function deleteFile($filename)
    {
        if (is_null($filename)) {
            throw new \Exception("The delete file name is null. It's impossible to delete file on Dropbox!");
        }
        $deletedFolder = $this->dropbox->delete($filename);
        if (is_null($deletedFolder)) {
            throw new \Exception("Delete file on Dropbox failed!");
        }
        return $deletedFolder->getName();
    }
    /**
     * Permet de retourner un lien de partage de fichier (url) placé dans la Dropbox
     *
     * Retourne le lien en cas de succès et null dans le cas contraire
     */
    public function createSharedLink($pathToFile)
    {
        if (is_null($pathToFile)) {
            throw new \Exception("The path name to create Dropbox shared link is null. It's impossible to create shared link from Dropbox!");
        }
        $response = $this->dropbox->postToAPI("/sharing/create_shared_link", [
            "path" => $pathToFile
        ]);
        if (is_null($response)) {
            throw new \Exception("Create shared link on Dropbox failed!");
        }
        $data = $response->getDecodedBody();

        return $data['url'];
    }
    /**
     * Permet de savoir s'il exist un lien de partage du fichier (url) placé dans la Dropbox
     *
     * Retourne true si le lien exist et false dans le cas contraire
     */
    public function hasSharedLinkFile($pathToFile)
    {
        if (is_null($pathToFile)) {
            throw new \Exception("The path file name to control if shared link file exist is null. It's impossible to control it!");
        }
        $link = $this->getSharedLinkFile($pathToFile);

        return ($link != null && $link != true);
    }
    /**
     * Permet de retourner un lien de partage de fichier (url) placé dans la Dropbox
     *
     * Retourne le lien en cas de succès en le créant s'il n'existe pas encore ou retourne null en cas d'erreur
     */
    public function getSharedLinkFileOrCreate($pathToFile)
    {
        if (is_null($pathToFile)) {
            throw new \Exception("The path file name to get shared link file is null. It's impossible to get shared link!");
        }
        if ($this->hasSharedLinkFile($pathToFile)) {
            $response = $this->dropbox->postToAPI("/sharing/list_shared_links", [
                "path" => $pathToFile
            ]);
            if (is_null($response)) {
                throw new \Exception("Get shared link file on Dropbox failed!");
            }
            $data = $response->getDecodedBody();
            if (count($data['links']) > 0) {
                $file = $data['links'][0]['path_lower'];
                $url = $data['links'][0]['url'];
                return $url;
            }
        } else {
            return $this->createSharedLink($pathToFile);
        }
    }
    /**
     * Permet de retourner un lien de partage de fichier (url) placé dans la Dropbox
     *
     * Retourne le lien en cas de succès et null dans le cas contraire
     */
    public function getSharedLinkFile($pathToFile)
    {
        if (is_null($pathToFile)) {
            throw new \Exception("The path file name to get shared link file is null. It's impossible to get shared link!");
        }
        $response = $this->dropbox->postToAPI("/sharing/list_shared_links", [
            "path" => $pathToFile
        ]);
        if (is_null($response)) {
            throw new \Exception("Get shared link file on Dropbox failed!");
        }
        $data = $response->getDecodedBody();
        if (count($data['links']) > 0) {
            $file = $data['links'][0]['path_lower'];
            $url = $data['links'][0]['url'];
            return $url;
        } else {
            return null;
        }
    }
    /**
     * Permet de supprimer le lien de partage du fichier placé dans la Dropbox
     *
     * Retourne le lien en cas de succès et false dans le cas contraire
     */
    public function deleteSharedLinkFile($sharedLinFile)
    {
        if (is_null($sharedLinFile)) {
            throw new \Exception("The shared link file is null. It's impossible to delete shared link file!");
        }
        $this->dropbox->postToAPI("/sharing/revoke_shared_link", [
            "url" => $sharedLinFile
        ]);
        return true;
    }
}
