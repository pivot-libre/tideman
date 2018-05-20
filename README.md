# Tideman
[![Travis status](https://img.shields.io/travis/pivot-libre/tideman/0.x.svg)](https://travis-ci.org/pivot-libre/tideman/)
[![Coveralls coverage](https://img.shields.io/coveralls/pivot-libre/tideman/0.x.svg)](https://coveralls.io/github/pivot-libre/tideman)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/d8a90cacf38b4d70afe97b1065cdb7e7)](https://www.codacy.com/app/pivot-libre/tideman?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=pivot-libre/tideman&amp;utm_campaign=Badge_Grade)
[![PDD status](http://www.0pdd.com/svg?name=pivot-libre/tideman)](http://www.0pdd.com/p?name=pivot-libre/tideman)

## Purpose
This algorithm takes a collection of rankings and produces a reasonably fair aggregate ranking using T.N. Tideman's Ranked Pairs Algorithm.

## Background
This documentation's intended audience is programmers. See [Wikipedia's Ranked Pairs article](https://en.wikipedia.org/wiki/Ranked_pairs) or Canadian MP Ron McKinnon's [condorcet.ca](https://condorcet.ca) for layperson-oriented explanations. 

## Summary
This algorithm first computes the difference in popular support between all pairs of candidates across all ballots. This pairwise difference between two candidates is called a margin. Next, the algorithm sorts the margins in order of descending difference. The algorithm then builds a graph data structure by iterating through the sorted list of margins from largest difference to smallest difference, adding an edge that points from the winning candidate to the losing candidate of each margin. If adding a margin's edge would introduce a cycle, the margin is ignored. The winning candidate is the candidate who has no edges pointing at them once the graph has been completed. In other words, the winner is the [source node](http://mathworld.wolfram.com/Source.html) in the completed graph. If multiple winners are desired, then the entire algorithm is repeated without considering candidates that have already won.

## Usage
Add `pivot-libre/tideman` as a dependency in your project's composer.json.
See [tests/RankedPairsCalculatorTest.php](tests/RankedPairsCalculatorTest.php) for example usage.

## Details

### Papers
 * Independence of Clones as a Criterion for Voting Rules. Tideman, T.N. Soc Choice Welfare (1987) 4: 185. [https://doi.org/10.1007/BF00433944](https://doi.org/10.1007/BF00433944)
 * Complete Independence of Clones in the Ranked Pairs Rule. Zavist, T.M. & Tideman, T.N. Soc Choice Welfare (1989) 6: 167. [https://doi.org/10.1007/BF00303170](https://doi.org/10.1007/BF00303170)

The original 1987 Ranked Pairs paper lacked a tie-breaking rule. The follow-up 1989 paper added a tie-breaking rule.

### Tie-Breaking
In elections with a small number of voters, it is common to encounter margins of equal difference. The sort order of margins of equal difference needs to be determined by a tie-breaking rule. In this case, the tie-breaking rule is to sort the tied margins according to a tie-breaking ballot. If the tie-breaking ballot contains ties itself, the ties within this ballot are broken randomly using [PHP's default random number generator](http://php.net/manual/en/function.mt-rand.php).

This implementation deviates from the 1989 paper in that it uses the tie-breaking ballot to break ties for all margins of identical strength, whereas the paper advocated that the tie-breaking rule be used only to break ties between margins whose differences were exactly zero.

If this implementation finds that a completed graph contains multiple source nodes, then all of the candidates associated with the source nodes are considered winners and their order is determined by the tie-breaking ballot.

### Additional Reading
 *  [Wikipedia's Ranked Pairs article](https://en.wikipedia.org/wiki/Ranked_pairs)
 * Canadian MP Ron McKinnon's [condorcet.ca](https://condorcet.ca) offers an excellent layperson-oriented survey of Ranked Pairs. Be sure to explore the various in-page dropdown sections and tabs, as some important parts of the site’s content are hidden inside.
 * The Cambridge Press Handbook of Computational Social Choice is available as a free pdf. Tideman’s Ranked Pairs Algorithm is described on page 98-101.
   * [Direct Link](http://www.cambridge.org/download_file/932961)
   * [Book Homepage](http://www.cambridge.org/us/academic/subjects/computer-science/artificial-intelligence-and-natural-language-processing/handbook-computational-social-choice?format=HB&isbn=9781107060432#GTKsebzTk5Wxs756.97)
 * An alternate description of the Tideman-Zavist tie-breaking rule is in [an electorama mailing list post by Dr. Markus Schulze](http://lists.electorama.com/pipermail/election-methods-electorama.com/2004-May/078350.html). The explanation is just a few lines long, starting with "Thomas Zavist suggested that...".

## Contributing
### Setup
 * Install PHP 7.1 and composer.
 * The library is known to work when the following PHP extensions are installed, though the minimal list of extensions is probably shorter (See [Issue #51](https://github.com/pivot-libre/tideman/issues/51)): ctype,curl,dom,iconv,json,mbstring,openssl,pdo,pdo_sqlite,phar,sqlite3,tokenizer,xmlreader,xmlwriter,zlib
### Working With the Code
 * Fork this repo.
 * Clone your fork.
 * Add this repo as upstream.
     * Examples:
         * `git remote add upstream git@github.com:pivot-libre/tideman.git`
         * `git remote add upstream https://github.com/pivot-libre/tideman.git`
 * Download the dependencies
     * `composer install`
 * Build the project
     * `vendor/bin/phing`
     * This command checks the syntax of the files, run tests, checks for formatting conformance, and performs static code quality analysis.
 * At the end of the output you should see `BUILD FINISHED`. You should not see `BUILD FAILLED`.
 
### Visualizing Test Coverage
 * Make sure you have [installed xdebug](https://xdebug.org/docs/install).
 * Run
 * `vendor/bin/phing coverage`
 * Open a web browser to `file:///<file-path-to-cloned-repo>tideman/build/coverage/index.html`
 * For example: `file:///home/john_doe/src/tideman/build/coverage/index.html`

### Sharing Your Work
 * Git `add`, and `commit` your local changes
 * Push your changes to your GitHub fork
     * `git push origin`
 * Create a pull request between your fork and the PivotLibre/tideman repo.
 
### Getting Updates
 * `git fetch upstream`
 * Depending on whether you are using a merge-based or rebase-based workflow you will run one of:
     * `git merge upstream/0.x`
     * `git rebase upstream/0.x`
     
