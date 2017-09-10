<?php

namespace PivotLibre\Tideman;

//https://docs.google.com/spreadsheets/d/1634wP6-N8GG2Fig-yjIOk7vPBn4AijXOrjq6Z2T1K8M/edit?usp=sharing
class TestScenario2
{

    public function getBallots()
    {
        return [
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("CS")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("RR")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("CS"),
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("FE")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("BT")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("CS")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("RR")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("RR")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("FE")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("MM")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("FE"),
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("CS")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("RR")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("RR")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("RR")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("FE")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("CS")
                ),
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("MM")
                )
            ),
            new NBallot(
                1,
                new CandidateList(
                    new Candidate("RR")
                ),
                new CandidateList(
                    new Candidate("BT")
                ),
                new CandidateList(
                    new Candidate("FE")
                ),
                new CandidateList(
                    new Candidate("MM")
                ),
                new CandidateList(
                    new Candidate("CS")
                )
            )
        ];
    }//end method
}//end class
