import sys
import json


def make_column_to_candidate_dict(header_row):
    my_dict = {}
    for colIndex, candidate in enumerate(header_row):
        my_dict[colIndex] = candidate.strip()

    return my_dict

def return_candidates_in_order(row, col_to_candidate_dict):
    ballot = []
    for i in range(0,len(row)):
        ballot.append([])

    for colIndex, rank in enumerate(row):
        candidate = col_to_candidate_dict[colIndex]
        int_rank = int(rank)
        ballot[int_rank-1].append(candidate)
    ballot = filter(lambda x: len(x) > 0, ballot)
    return ballot

def split_line(line):
    return line.split('\t')

def convert_csv(filename):
    return convert_csv_to_php(filename) 

def convert_csv_to_json(filename):
    ballot_arrays = get_ballot_arrays(filename)
    objects = []
    for ballot_array in ballot_arrays:
        ballot_object = {'count': 1, 'values': ballot_array}

    print(json.dumps(objects))

def convert_csv_to_php(filename):
    class_text = ''
    with open('TestScenarioHeader.php.fragment', 'r') as class_header:
        class_text += class_header.read()
    ballot_arrays = get_ballot_arrays(filename)
    class_text += generate_php(ballot_arrays)
    with open('TestScenarioFooter.php.fragment', 'r') as class_footer:
        class_text += class_footer.read().rstrip()

    print class_text

def generate_php(ballot_arrays):
    ballots = []
    for ballot in ballot_arrays:
        ballots.append(generate_one_ballot_php(ballot))
    
    return '        return [\n' + ',\n'.join(ballots) + '\n        ];\n'

def generate_one_ballot_php(ballot):
    php = '            new NBallot(\n                1,\n'
    candidate_lists = []
    for group in ballot:
        candidate_list = '                new CandidateList(\n'
        candidates = []
        for candidate in group:
            candidates.append('                    new Candidate("' + candidate + '")')

        candidate_list += ',\n'.join(candidates)
        candidate_list += '\n                )'
        candidate_lists.append(candidate_list)
    
    php += ',\n'.join(candidate_lists)
    php += '\n            )'
    return php


def get_ballot_arrays(filename):
    ballots = []
    header = True
    ids = False
    with open(filename, 'r') as csv:
        for line in csv.readlines():
            row = split_line(line)
            if header:
                header = False
                ids = True
            elif ids:
                col_to_candidate_dict = make_column_to_candidate_dict(row)
                ids = False
            else:
                ballot = return_candidates_in_order(row, col_to_candidate_dict)
                ##print ballot
                ballots.append(ballot)
    
    return ballots

if __name__  == '__main__':
    convert_csv(sys.argv[1])

