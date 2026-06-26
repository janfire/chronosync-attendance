import sys

def check_braces(filename):
    with open(filename, 'r') as f:
        lines = f.readlines()
        
    depth = 0
    in_string = False
    string_char = ''
    in_comment = False
    
    for line_num, line in enumerate(lines, 1):
        if line_num < 180 or line_num > 420: # Only care about startFaceDetection
            continue
            
        for i, char in enumerate(line):
            if not in_string and not in_comment:
                if char == '/' and i+1 < len(line):
                    if line[i+1] == '/':
                        break # line comment
                    elif line[i+1] == '*':
                        in_comment = True
                elif char in ["'", '"', '`']:
                    in_string = True
                    string_char = char
                elif char == '{':
                    depth += 1
                    print(f"Line {line_num}: {{ (depth {depth}) - {line.strip()[:40]}")
                elif char == '}':
                    print(f"Line {line_num}: }} (depth {depth}) - {line.strip()[:40]}")
                    depth -= 1
            elif in_string:
                if char == string_char and line[i-1] != '\\':
                    in_string = False
            elif in_comment:
                if char == '*' and i+1 < len(line) and line[i+1] == '/':
                    in_comment = False

if __name__ == '__main__':
    check_braces('resources/js/attendance-clock.js')
