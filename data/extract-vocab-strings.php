<?php
/**
 * Extract labels and comments from bundled vocabularies and output POT message IDs.
 *
 * Run the gulp task for the template.pot file first, then this.
 * vocab.pot will be in the language directory.
 * Then, msgcat --use-first vocab.pot template.pot -o template.pot
 * to remove duplicate entries
 */
require dirname(dirname(dirname(__DIR__))) . '/bootstrap.php';
$config = require OMEKA_PATH . '/application/config/application.config.php';
$application = Laminas\Mvc\Application::init($config);
$services = $application->getServiceManager();

$rdfImporter = $services->get('Omeka\RdfImporter');

$vocabs = [
    [
        'vocabulary' => [
            'o:namespace_uri' => 'http://www.w3.org/ns/ldp#',
            'o:prefix' => 'ldp',
            'o:label' => 'Linked Data Platform Vocabulary',
            'o:comment' => 'Vocabulary for a Linked data Platform. Used by Fedora.',
        ],
        'strategy' => 'file',
        'file' => OMEKA_PATH . '/modules/FedoraConnector/data/ldp.rdf',
        'format' => 'rdfxml',
    ],
    [
        'vocabulary' => [
            'o:namespace_uri' => 'http://fedora.info/definitions/v4/repository#',
            'o:prefix' => 'fedora',
            'o:label' => 'Fedora Commons Repository Ontology',
            'o:comment' => 'Ontology for the Fedora data model.',
        ],
        'strategy' => 'file',
        'file' => OMEKA_PATH . '/modules/FedoraConnector/data/repository.rdf',
        'format' => 'rdfxml',
    ],
];

// Build the msgids and their comments.
$msgids = [];
foreach ($vocabs as $vocab) {
    $members = $rdfImporter->getMembers($vocab['strategy'], $vocab['vocabulary']['o:namespace_uri'], $vocab);

    foreach ($members['classes'] as $localName => $info) {
        $msgids[$info['label']][] = sprintf(
            '#. Class label for %s:%s',
            $vocab['vocabulary']['o:label'],
            $localName
        );
        $msgids[$info['comment']][] = sprintf(
            '#. Class comment for %s:%s',
            $vocab['vocabulary']['o:label'],
            $localName
        );
    }

    foreach ($members['properties'] as $localName => $info) {
        $msgids[$info['label']][] = sprintf(
            '#. Property label for %s:%s',
            $vocab['vocabulary']['o:label'],
            $localName
        );
        $msgids[$info['comment']][] = sprintf(
            '#. Property comment for %s:%s',
            $vocab['vocabulary']['o:label'],
            $localName
        );
    }
}

// Output the POT file.
$template = <<<POT
msgid "%s"
msgstr ""


POT;

$output = '';
foreach ($msgids as $msgid => $comments) {
    foreach ($comments as $comment) {
        $output .= $comment . PHP_EOL;
    }
    $output .= sprintf($template, addcslashes($msgid, "\n\"\\"));
}

file_put_contents(OMEKA_PATH . '/modules/FedoraConnector/language/vocab.pot', $output);
