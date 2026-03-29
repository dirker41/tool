def main():
    output_row=""
    for i in range(30, 38):
        output_row+="\033[%dm%d\033[0m  "%((i, i))

    print(output_row)

    output_row=""
    for i in range(40, 48):
        output_row+="\033[%dm%d\033[0m  "%((i, i))

    print(output_row)

    output_row=""
    for i in range(90, 98):
        output_row+="\033[%dm%d\033[0m  "%((i, i))

    print(output_row)



main()