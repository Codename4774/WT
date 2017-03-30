#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h> 
#include <sys/socket.h>
#include <netinet/in.h>
#include <errno.h>
#include <signal.h>
#include <sys/stat.h>
//#include <sys/mman.h>

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

#define MAXCLIENTCOUNT 100

int tryAddClient(int serverSocketFd, int clientsSockets[], int *countClients)
{
	int buffFd = accept(serverSocketFd, NULL, NULL);
	if (buffFd < 0)
	{
		return -1;
	}
	else
	{
		if ( (*countClients) < MAXCLIENTCOUNT)
		{
			clientsSockets[*countClients] = buffFd;
			write(buffFd, "approved", 8);
			++(*countClients);
			return 0;
		}
		else
		{
			return -1;
		}

	}
	
}

int freeResources(int serverSocketFd,  int clientsSockets[], int countClients)
{
	close(serverSocketFd);
	int i = 0;
	for (i; i < countClients; i++)
	{
		close(clientsSockets[i]);
	}
	return 0;
}

int clientsFd[MAXCLIENTCOUNT];
int countClient = 0;
int serverSocketFd;

void signalHandler(int signalNo)
{
	freeResources(serverSocketFd, clientsFd, countClient);
	printf("All is free\n\r");
	exit(0);
}

#define QUEUELENGTH 5

long int getFileLength()
{
	long int res;
	struct stat file_info;
	
	stat("/tmp/fileMessages.txt", &file_info);
	res = file_info.st_size;
	return res;
}

int main(int argc, char * argv[])
{

	struct sockaddr_in serverAddr;
	
	signal(SIGTSTP, signalHandler);
	
	serverSocketFd = socket(AF_INET, SOCK_STREAM, 0);
	if (serverSocketFd < 0)
	{
		printError(errno, "socket");
	}
	
	serverAddr = fillSockAddrStruct(AF_INET, atoi(argv[1]), INADDR_ANY);
	
	if ( bind(serverSocketFd, (struct sockaddr *) &serverAddr, sizeof(serverAddr)) < 0)
	{
		printError(errno, "bind");
	}
	else
	{
		printf("Binding succes\n\r");
	}
	
	listen(serverSocketFd, QUEUELENGTH);
	
	struct sockaddr_in clientAddr;
	socklen_t clientAddrStructLen;
	int countSucces = -1;
	char buffer[256];
	int clientFd = 1;
	
	
	while (1)
	{
		int i;
		
		while ((clientFd = accept(serverSocketFd, NULL, NULL)) > 0)
		{
			printf("There is a new client N%d\n\r", clientFd);
			pid_t currPID = fork();


			if (currPID > 0)
			{
				close(serverSocketFd);
				
				bzero(buffer, 256);
				sprintf(buffer, "%d", clientFd);
				send(clientFd, buffer, 255, 0 );
				
				pid_t currSendPID = fork();
				if (currSendPID > 0)
				{
					FILE *fileMessages = fopen("/tmp/fileMessages.txt", "r+");
					
					printf("Send procces to client N%d has created\n\r", clientFd);
					
					long int prevOffset = getFileLength();
					long int offset;
					while (1)
					{
						offset = getFileLength();
						//printf("%d\t", offset);
						if (prevOffset != offset)
						{
							bzero(buffer, 256);
							printf("%ld %ld\n\r", prevOffset, offset);
							fseek(fileMessages, prevOffset, SEEK_SET);
							fread(buffer, sizeof(char), offset - prevOffset, fileMessages);
							printf("Need to send client N%d: %s", clientFd, buffer);
							//char numbCLient[10];
							
							//if (buffer[0] == )
							send(clientFd, buffer, 255, 0);
							prevOffset = offset;
							fseek(fileMessages, 0, SEEK_END);
						}
					}
					
				}
				else
				{
					FILE *fileMessages = fopen("/tmp/fileMessages.txt", "a");
					
					printf("Receive procces to client N%d has created\n\r", clientFd);
					while (1)
					{
						bzero(buffer, 256);
						countSucces = recv(clientFd, buffer, 255, 0);
						if (countSucces > 0)
						{
							
							printf("Client N%d: %s\n\r", clientFd, buffer);
							fprintf(fileMessages, "%d:", clientFd);
							if (fwrite(buffer, sizeof(char), strlen(buffer), fileMessages) <= 0)
							{
								printf("Error writing\n\r");
							}
							fflush(fileMessages);
						//prevOffset = ftell(fileMessages);
						}
					}
				}	
			}
		}
		
	}
	return 0;
}
