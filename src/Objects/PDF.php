<?php

/**
 * Core Framework - PDF
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Mpdf\Mpdf;
use Exception;

class PDF {

    // Constants
    const MODES = ['utf-8'];
    const FORMATS = ['Letter', 'Legal', 'A4', 'A3', 'A5'];
    const ORIENTATIONS = ['P', 'L'];
    const DPI = [72, 96, 150, 300];
    const FONTS = ['Helvetica', 'Courier', 'Times', 'Symbol', 'ZapfDingbats'];
    const PERMISSIONS = ['print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-highres'];
    const ENCRYPTIONS = [40, 128, 256];

    // Properties
    private $pdf;
    private $html;
    private $mode = 'utf-8';
    private $format = 'Letter';
    private $orientation = 'P';
    private $dpi = 96;
    private $font = 'Helvetica';
    private $permissions = ['print','print-highres','annot-forms'];
    private $encryption = 128;
    private $margins = [15, 15, 15, 15];
    private $title;
    private $author;
    private $creator;
    private $subject;
    private $keywords;
    private $watermark;
    private $passwordOwner;
    private $passwordUser = '';
    private $path;
    private $values = [];
    private $letterhead;

    /**
     * Constructor
     */
    public function __construct()
    {}

    /**
     * Import HTML from a file
     *
     * @param string $path
     * @return self
     */
    public function template(string $path): self
    {
        // Check if the file exists
        if(file_exists($path)){

            // Retrieve the HTML content
            $this->html = file_get_contents($path);
        }

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF mode
     *
     * @param string $mode
     * @return self
     */
    public function mode(string $mode): self
    {
        // Set the mode
        $this->mode = in_array($mode,self::MODES) ? $mode : $this->mode;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF format
     *
     * @param string $format
     * @return self
     */
    public function format(string $format): self
    {
        // Set the format
        $this->format = in_array($format,self::FORMATS) ? $format : $this->format;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF orientation
     *
     * @param string $orientation
     * @return self
     */
    public function orientation(string $orientation): self
    {
        // Set the orientation
        $this->orientation = in_array($orientation,self::ORIENTATIONS) ? $orientation : $this->orientation;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF DPI
     *
     * @param int $dpi
     * @return self
     */
    public function dpi(int $dpi): self
    {
        // Set the DPI
        $this->dpi = in_array($dpi,self::DPI) ? $dpi : $this->dpi;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF font
     *
     * @param string $font
     * @return self
     */
    public function font(string $font): self
    {
        // Set the font
        $this->font = in_array($font,self::FONTS) ? $font : $this->font;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF permissions - Allow
     *
     * @param string $permission
     * @return self
     */
    public function allow(string $permission): self
    {
        // Set the permission
        $this->permissions[] = in_array($permission,self::PERMISSIONS) ? $permission : $this->permissions;

        // Filter unique values
        $this->permissions = array_unique($this->permissions);

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF permissions - Deny
     *
     * @param string $permission
     * @return self
     */
    public function deny(string $permission): self
    {
        // Remove the permission
        $this->permissions = array_diff($this->permissions, [$permission]);

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF encryption
     *
     * @param int $encryption
     * @return self
     */
    public function encryption(int $encryption): self
    {
        // Set the encryption
        $this->encryption = in_array($encryption,self::ENCRYPTIONS) ? $encryption : $this->encryption;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF margins
     *
     * @param int $top
     * @param int $header
     * @param int $bottom
     * @param int $footer
     * @return self
     */
    public function margins(int $top, int $header, int $bottom, int $footer): self
    {
        // Set the margins
        $this->margins = [$top, $header, $bottom, $footer];

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF title
     *
     * @param string $title
     * @return self
     */
    public function title(string $title): self
    {
        // Set the title
        $this->title = $title;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF author
     *
     * @param string $author
     * @return self
     */
    public function author(string $author): self
    {
        // Set the author
        $this->author = $author;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF creator
     *
     * @param string $creator
     * @return self
     */
    public function creator(string $creator): self
    {
        // Set the creator
        $this->creator = $creator;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF subject
     *
     * @param string $subject
     * @return self
     */
    public function subject(string $subject): self
    {
        // Set the subject
        $this->subject = $subject;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF keywords
     *
     * @param string $keywords
     * @return self
     */
    public function keywords(string $keywords): self
    {
        // Set the keywords
        $this->keywords = $keywords;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF watermark
     *
     * @param string $watermark
     * @return self
     */
    public function watermark(string $watermark): self
    {
        // Set the watermark
        $this->watermark = $watermark;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF owner password
     *
     * @param string $password
     * @return self
     */
    public function owner(string $password): self
    {
        // Set the password
        $this->passwordOwner = $password;

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF user password
     *
     * @param string $password
     * @return self
     */
    public function user(?string $password = null): self
    {
        // Set the password
        $this->passwordUser = $password ?? '';

        // Return the instance
        return $this;
    }

    /**
     * Set the PDF path
     *
     * @param string $path
     * @return self
     */
    public function path(string $path): self
    {
        // Set the path
        $this->path = str_replace('.pdf', '', $path) . '.pdf';

        // Return the instance
        return $this;
    }

    /**
     * Set the value of a variable
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function variable(string $key, $value): self
    {
        // Set the value
        $this->values[$key] = $value;

        // Return the instance
        return $this;
    }

    /**
     * Set the values of the variables
     *
     * @param array $values
     * @return self
     */
    public function variables(array $values): self
    {
        // Set the values
        $this->values = array_merge($this->values, $values);

        // Return the instance
        return $this;
    }

    /**
     * Set the letterhead path
     *
     * @param string $path
     * @return self
     */
    public function letterhead(?string $path): self
    {
        // Set the letterhead path
        $this->letterhead = $path;

        // Return the instance
        return $this;
    }

    /**
     * Retrieve Document's Variables
     *
     * @return array
     */
    public function getVars(): array
    {
        // Regular expression to match the variables in the format %VAR%
        $pattern = '/%([^%]+)%/';

        // Find all matches
        preg_match_all($pattern, $this->html, $matches);

        // The variables are in the second element of the $matches array
        $variables = array_unique($matches[0]);

        // Return the variables
        return $variables;
    }

    /**
     * Replace Variables from an array
     *
     * @param string $string
     * @param array $values
     * @return string
     */
    private function replace(string $string, array $values): string
    {
        // Replace the placeholders with the corresponding data
        foreach ($values as $key => $value) {
            $string = str_replace('%' . $key . '%', $value ?? '', $string);
        }

        // Return the string
        return $string;
    }

    /**
     * Generate the PDF
     *
     * @return string
     */
    public function generate(): ?string
    {
        // Create an instance of the mPDF class
        $pdf = new Mpdf([
            'mode' => $this->mode,
            'format' => $this->format,
            'orientation' => $this->orientation,
            'dpi' => $this->dpi,
        ]);

        // Check if a letterhead is set
        if($this->letterhead && is_file($this->letterhead)){

            // Count the number of pages in the letterhead
            $count = $pdf->SetSourceFile($this->letterhead);

            // Import the first page of the letterhead (you can adjust the page number if needed)
            $template = $pdf->ImportPage($count);

            // Use the imported page as a background template
            $pdf->SetDocTemplate($this->letterhead, true);
        }

        // Set the PDF properties
        $this->title ? $pdf->SetTitle($this->title) : null;
        $this->author ? $pdf->SetAuthor($this->author) : null;
        $this->creator ? $pdf->SetCreator($this->creator) : null;
        $this->subject ? $pdf->SetSubject($this->subject) : null;
        $this->keywords ? $pdf->SetKeywords($this->keywords) : null;
        $this->watermark ? $pdf->SetWatermarkText($this->watermark) : null;
        $pdf->showWatermarkText = (!empty($this->watermark) && !is_null($this->watermark));

        // Set the PDF Security
        if($this->passwordOwner){
            $pdf->SetProtection(
                $this->permissions,
                $this->passwordUser,
                $this->passwordOwner,
                $this->encryption
            );
        }

        // Set the default font
        $pdf->SetDefaultFont($this->font);

        // Set the margins
        $pdf->SetMargins($this->margins[0], $this->margins[1], $this->margins[2], $this->margins[3]);

        // Enable the use of active forms
        $pdf->useActiveForms = true;

        // Generate the final HTML
        $html = $this->replace($this->html, $this->values);

        // Write the HTML content to the PDF
        $pdf->WriteHTML($html);

        // Check if a path is set
        if($this->path){

            // Create the path
            if(!is_dir(dirname($this->path))){
                mkdir(dirname($this->path), 0777, true);
            }

            // Save the PDF
            $pdf->Output($this->path, 'F');
        }

        // Return the file path
        return $this->path;
    }
}
