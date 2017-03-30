#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <errno.h>
#include <spawn.h> 

void printError(int errorCode, char *commandName)
{
	char *errorInfo = strerror(errorCode);
	fprintf(stderr, "%s:%s\n\r", commandName, errorInfo);
	errno = 0;
}

struct sockaddr_in fillSockAddrStruct(short sin_family, unsigned short sin_port, unsigned long sin_addr)
{
	struct sockaddr_in res;
	
	bzero((char *) &res, sizeof(res));
	res.sin_family = sin_family;
	res.sin_port = htons(sin_port);
	res.sin_addr.s_addr = sin_addr;
	
	return res;
}

int getIdMessage(const char *buffer, char **msgPointer)
{
	char bufferNumb[20];
	int i = 0;
	
	while(buffer[i] != ':')
	{
		bufferNumb[i] = buffer[i];
		i++;
	}
	*msgPointer = &buffer[i + 1];
	bufferNumb[i] = '\0';
	return atoi(bufferNumb);
}

extern char **environ;

int main(int argc, char * argv[])
{
	int clientSocketFd;
	struct sockaddr_in serverAddr;
	struct hostent *serverHostInfo;
	
	clientSocketFd = socket(AF_INET, SOCK_STREAM, 0);
	if (clientSocketFd < 0)
	{
		printError(errno, "socket");
	}
	
	serverHostInfo = gethostbyname(argv[1]);
	if (serverHostInfo == NULL)
	{
		printError(errno, "gethostbyname");
	}
	
	bzero( (char *)&serverAddr, sizeof(serverAddr));
	
	unsigned long buffAddr;
	
	bcopy( (char *)serverHostInfo->h_addr, (char *)&buffAddr, serverHostInfo->h_length );
	serverAddr = fillSockAddrStruct(AF_INET, atoi(argv[2]), buffAddr);
	
	char buffer[256];
	int idNumb;
	
    	if ( connect( clientSocketFd, (struct sockaddr *) &serverAddr, sizeof(serverAddr) ) < 0) 
       	{
		printError(errno, "connect");
	}
	else
	{
		recv(clientSocketFd, buffer, 255, 0);
		idNumb = atoi(buffer);
		printf("Connect succes. My ID numb = %d\n\r", idNumb);
	}
	

	
	
	int countSucces;
	buffer[0] = 0;
	pid_t currPID = fork();
	char bufferInput[256];
	char *msgPointer;
	
	
	if (currPID == 0)
	{
		//char *argvCon[] = {"gnome-terminal"}; 
		//execlp("gnome-terminal", "e", "foo");

		while ( 1 )
		{
			bzero(buffer,256);
			//strcat(buffer, argv[3]);
			//strcat(buffer, ": ");

			fgets(bufferInput, 255, stdin);
			fprintf(stdin, "%s: ", argv[3]);


			strcat(buffer, bufferInput);
			countSucces = send(clientSocketFd, buffer, 255, 0);
		}
	}
	else
	{
		while (1)
		{
			system("gnome-terminal");
			countSucces = recv(clientSocketFd, buffer, 255, 0);
			if (countSucces > 0)
			{
				if (idNumb != getIdMessage(buffer, &msgPointer))
				{
					fputs(msgPointer, stdout);
					fflush(stdin);
				}
			}
		}	
	}
	close(clientSocketFd);
	return 0;
}
